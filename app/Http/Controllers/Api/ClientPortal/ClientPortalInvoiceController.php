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

    public function download(Request $request, Invoice $invoice): JsonResponse
    {
        $invoice = $this->authorizedInvoice($request, $invoice);

        return response()->json([
            'message' => 'Download invoice portal client belum diaktifkan.',
            'invoice_id' => $invoice->id,
        ], 501);
    }

    private function authorizedInvoice(Request $request, Invoice $invoice): Invoice
    {
        /** @var ClientPortalAccount $account */
        $account = $request->user();

        abort_unless($invoice->client_id === $account->client_id, 404);

        return $invoice;
    }
}
