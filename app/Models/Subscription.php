<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

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
        'discount_percent' => 'decimal:2',
    ];

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

    /**
     * Get the effective price for billing.
     * Uses custom_price if set, otherwise calculates from package price.
     */
    public function getEffectivePriceAttribute(): float
    {
        $basePrice = $this->custom_price ?? ($this->package?->price * $this->billing_period_months) ?? 0;

        if ($this->discount_percent) {
            $discount = $basePrice * ($this->discount_percent / 100);
            return $basePrice - $discount;
        }

        return $basePrice;
    }
}
