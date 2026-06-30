<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use LogsModelActivity;

    protected $fillable = [
        'invoice_id',
        'amount',
        'payment_method',
        'payment_date',
        'reference_number',
        'proof_path',
        'notes',
        'status',
        'verified_by',
        'verified_at',
        'rejected_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'verified_at' => 'datetime',
    ];

    protected string $activitylogEntityName = 'pembayaran';

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function updateInvoiceStatus(): void
    {
        $invoice = $this->invoice;
        if (! $invoice) {
            return;
        }

        $totalPayments = $invoice->payments()->where('status', 'verified')->sum('amount');
        $invoiceTotal = $invoice->total_amount;

        if ($totalPayments >= $invoiceTotal) {
            $invoice->update([
                'status' => 'paid',
                'paid_at' => $invoice->payments()->latest('payment_date')->value('payment_date') ?? now(),
            ]);
        } elseif ($totalPayments > 0) {
            $invoice->update([
                'status' => 'partially_paid',
                'paid_at' => null,
            ]);
        } else {
            $status = $invoice->due_date && $invoice->due_date->startOfDay()->lt(now()->startOfDay())
                ? 'overdue'
                : 'unpaid';

            $invoice->update([
                'status' => $status,
                'paid_at' => null,
            ]);
        }
    }
}
