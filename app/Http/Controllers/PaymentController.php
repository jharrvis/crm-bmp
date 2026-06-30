<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:payments.view')->only(['index', 'show']);
        $this->middleware('permission:payments.create')->only(['create', 'store']);
        $this->middleware('permission:payments.update')->only(['edit', 'update']);
        $this->middleware('permission:payments.delete')->only(['destroy']);
        $this->middleware('permission:payments.verify')->only(['verify', 'reject']);
    }

    public function index(Request $request)
    {
        $query = Payment::with(['invoice.client', 'verifiedBy'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('invoice_id')) {
            $query->where('invoice_id', $request->invoice_id);
        }

        $payments = $query->paginate(20);

        return view('payments.index', compact('payments'));
    }

    public function create(Request $request)
    {
        $invoiceId = $request->invoice_id;
        $invoice = null;

        if ($invoiceId) {
            $invoice = Invoice::with('client')->findOrFail($invoiceId);
        }

        return view('payments.create', compact('invoice'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string|max:50',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'proof_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $proofPath = null;
            if ($request->hasFile('proof_file')) {
                $proofPath = $request->file('proof_file')->store('payment-proofs', 'public');
            }

            // If created by someone with verify permission, auto-verify it
            $status = $request->user()->can('payments.verify') ? 'verified' : 'pending';
            $verifiedBy = $status === 'verified' ? $request->user()->id : null;
            $verifiedAt = $status === 'verified' ? now() : null;

            $payment = Payment::create([
                'invoice_id' => $request->invoice_id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'payment_date' => $request->payment_date,
                'reference_number' => $request->reference_number,
                'proof_path' => $proofPath,
                'notes' => $request->notes,
                'status' => $status,
                'verified_by' => $verifiedBy,
                'verified_at' => $verifiedAt,
            ]);

            if ($status === 'verified') {
                $payment->updateInvoiceStatus();
            }

            DB::commit();

            return redirect()->route('invoices.show', $request->invoice_id)
                ->with('success', 'Pembayaran berhasil dicatat.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal mencatat pembayaran: '.$e->getMessage())->withInput();
        }
    }

    public function verify(Payment $payment)
    {
        if ($payment->status === 'verified') {
            return back()->with('error', 'Pembayaran sudah diverifikasi.');
        }

        DB::beginTransaction();
        try {
            $payment->update([
                'status' => 'verified',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);

            $payment->updateInvoiceStatus();

            DB::commit();

            return back()->with('success', 'Pembayaran berhasil diverifikasi.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memverifikasi: '.$e->getMessage());
        }
    }

    public function reject(Request $request, Payment $payment)
    {
        $request->validate([
            'rejected_reason' => 'required|string|max:255',
        ]);

        if ($payment->status === 'verified') {
            return back()->with('error', 'Pembayaran yang sudah diverifikasi tidak dapat ditolak.');
        }

        $payment->update([
            'status' => 'rejected',
            'rejected_reason' => $request->rejected_reason,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Pembayaran berhasil ditolak.');
    }
}
