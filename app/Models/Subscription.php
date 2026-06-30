<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory, LogsModelActivity;

    protected $fillable = [
        'client_id',
        'package_id',
        'subscription_code',
        'status',
        'installed_at',
        'billing_cycle_day',
        'next_billing_date',
        'terminated_at',
        'termination_reason',
        'price_at_subscription',
        'custom_price',
        'billing_period_months',
        'uses_ppn',
        'ppn_amount',
        'uses_pph23',
        'pph23_amount',
        'discount_percent',
        'discount_notes',
        'notes',
    ];

    protected $casts = [
        'installed_at' => 'date',
        'next_billing_date' => 'date',
        'terminated_at' => 'date',
        'price_at_subscription' => 'decimal:2',
        'custom_price' => 'decimal:2',
        'uses_ppn' => 'boolean',
        'ppn_amount' => 'decimal:2',
        'uses_pph23' => 'boolean',
        'pph23_amount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
    ];

    protected string $activitylogEntityName = 'langganan';

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    // One-to-one detail relations
    public function connectivity()
    {
        return $this->hasOne(SubscriptionConnectivity::class);
    }

    public function hosting()
    {
        return $this->hasOne(SubscriptionHosting::class);
    }

    public function domain()
    {
        return $this->hasOne(SubscriptionDomain::class);
    }

    public function topology()
    {
        return $this->hasOne(SubscriptionTopology::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Get the effective price for billing.
     * Uses custom_price if set, otherwise calculates from package price.
     */
    public function getEffectivePriceAttribute(): float
    {
        $basePrice = $this->custom_price ?? ($this->package?->price * $this->billing_period_months) ?? 0;
        $ppnAmount = $this->uses_ppn
            ? (float) ($this->ppn_amount ?? round(self::calculatePpnAmount($basePrice), 2))
            : 0.0;
        $pph23Amount = $this->uses_pph23
            ? (float) ($this->pph23_amount ?? round(self::calculatePph23Amount($basePrice), 2))
            : 0.0;

        return ((float) $basePrice + $ppnAmount) - $pph23Amount;
    }

    public function getBasePriceAttribute(): float
    {
        return (float) ($this->custom_price ?? ($this->package?->price * $this->billing_period_months) ?? 0);
    }

    public static function calculatePpnAmount(float $basePrice): float
    {
        return $basePrice * (setting('billing.ppn_rate', 11) / 100);
    }

    public static function calculatePph23Amount(float $basePrice): float
    {
        return $basePrice * (setting('billing.pph23_rate', 2) / 100);
    }
}
