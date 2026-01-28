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
        'notes',
    ];

    protected $casts = [
        'installed_at' => 'date',
        'next_billing_date' => 'date',
        'terminated_at' => 'date',
        'price_at_subscription' => 'decimal:2',
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
}
