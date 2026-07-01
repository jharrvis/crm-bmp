<?php

namespace App\Http\Controllers\Api\ClientPortal;

use App\Http\Controllers\Controller;
use App\Models\ClientPortalAccount;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientPortalInvoiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var ClientPortalAccount $account */
        $account = $request->user();

        $validated = $request->validate([
            'status' => 'nullable|in:paid,unpaid,overdue',
        ]);

        $query = Invoice::query()
            ->with('items')
            ->where('client_id', $account->client_id)
            ->latest('invoice_date');

        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        return response()->json([
            'data' => $query->get()->map(fn ($invoice) => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'invoice_date' => $invoice->invoice_date?->toDateString(),
                'due_date' => $invoice->due_date?->toDateString(),
                'status' => $invoice->status,
                'total_amount' => (float) $invoice->total_amount,
                'paid_at' => $invoice->paid_at?->toIso8601String(),
                'items_count' => $invoice->items->count(),
            ]),
        ]);
    }

    public function show(Request $request, Invoice $invoice): JsonResponse
    {
        $invoice = $this->authorizedInvoice($request, $invoice);
        $invoice->load('items');

        return response()->json([
            'data' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'invoice_date' => $invoice->invoice_date?->toDateString(),
                'due_date' => $invoice->due_date?->toDateString(),
                'status' => $invoice->status,
                'total_amount' => (float) $invoice->total_amount,
                'paid_at' => $invoice->paid_at?->toIso8601String(),
                'notes' => $invoice->notes,
                'items' => $invoice->items->map(fn ($item) => [
                    'id' => $item->id,
                    'description' => $item->description,
                    'quantity' => (int) $item->qty,
                    'price' => (float) $item->amount,
                    'subtotal' => (float) $item->total,
                ]),
            ],
        ]);
    }

    public function download(Request $request, Invoice $invoice)
    {
        $invoice = $this->authorizedInvoice($request, $invoice);

        $pdfService = new \App\Services\InvoicePdfService;

        return $pdfService->download($invoice);
    }

    public function paymentConfirmation(Request $request, Invoice $invoice): JsonResponse
    {
        $invoice = $this->authorizedInvoice($request, $invoice);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string|max:50',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'proof_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'notes' => 'nullable|string',
        ]);

        $proofPath = null;
        if ($request->hasFile('proof_file')) {
            $proofPath = $request->file('proof_file')->store('payment-proofs', 'public');
        }

        $payment = \App\Models\Payment::create([
            'invoice_id' => $invoice->id,
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'payment_date' => $validated['payment_date'],
            'reference_number' => $validated['reference_number'] ?? null,
            'proof_path' => $proofPath,
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending', // Menunggu verifikasi tim finance
        ]);

        // (Opsional) Kirim notifikasi internal ke tim finance

        return response()->json([
            'message' => 'Konfirmasi pembayaran berhasil dikirim dan menunggu verifikasi.',
            'data' => [
                'payment_id' => $payment->id,
                'status' => $payment->status,
            ],
        ], 201);
    }

    private function authorizedInvoice(Request $request, Invoice $invoice): Invoice
    {
        /** @var ClientPortalAccount $account */
        $account = $request->user();

        abort_unless($invoice->client_id === $account->client_id, 404);

        return $invoice;
    }
}
