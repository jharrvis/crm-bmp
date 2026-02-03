<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionDomain extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'domain_name',
        'registrar',
        'auth_code_encrypted',
        'registered_at',
        'expires_at',
        'dns_records',
        'notes',
    ];

    protected $casts = [
        'registered_at' => 'date',
        'expires_at' => 'date',
        'dns_records' => 'array',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Get the decrypted auth code.
     */
    public function getAuthCodeAttribute(): ?string
    {
        return $this->auth_code_encrypted ? decrypt($this->auth_code_encrypted) : null;
    }
}
