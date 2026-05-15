<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'user_id',
        'client_code',
        'registered_at',
        'name',
        'type',
        'identity_number',
        'address',
        'city',
        'postal_code',
        'latitude',
        'longitude',
        'status',
        'notes',
    ];

    protected $casts = [
        'registered_at' => 'date',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contacts()
    {
        return $this->hasMany(ClientContact::class);
    }

    // Helper to get primary contact
    public function primaryContact()
    {
        return $this->hasOne(ClientContact::class)->where('is_primary', true);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function portalAccount()
    {
        return $this->hasOne(ClientPortalAccount::class);
    }
}
