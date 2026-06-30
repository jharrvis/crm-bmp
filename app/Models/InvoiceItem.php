<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use LogsModelActivity;

    protected $fillable = [
        'invoice_id',
        'subscription_id',
        'description',
        'amount',
        'qty',
        'total',
        'is_prorated',
        'proration_start_date',
        'proration_end_date',
        'proration_days',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'total' => 'decimal:2',
        'is_prorated' => 'boolean',
        'proration_start_date' => 'date',
        'proration_end_date' => 'date',
        'proration_days' => 'integer',
    ];

    protected string $activitylogEntityName = 'item tagihan';

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function getBillingBaseAmountAttribute(): float
    {
        if (! $this->subscription) {
            return (float) $this->amount;
        }

        $baseAmount = (float) $this->subscription->base_price;
        $effectiveAmount = (float) $this->subscription->effective_price;
        $storedAmount = (float) $this->amount;

        if (abs($storedAmount - $effectiveAmount) < 0.01) {
            return $baseAmount;
        }

        return $storedAmount;
    }

    public function getBillingLineTotalAttribute(): float
    {
        return $this->billing_base_amount * max(1, (int) $this->qty);
    }

    public function getBillingPpnAmountAttribute(): float
    {
        if (! $this->subscription || ! $this->subscription->uses_ppn) {
            return 0.0;
        }

        return (float) ($this->subscription->ppn_amount ?? 0) * max(1, (int) $this->qty);
    }

    public function getBillingPph23AmountAttribute(): float
    {
        if (! $this->subscription || ! $this->subscription->uses_pph23) {
            return 0.0;
        }

        return (float) ($this->subscription->pph23_amount ?? 0) * max(1, (int) $this->qty);
    }
}
