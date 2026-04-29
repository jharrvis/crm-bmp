<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientPortalOtp extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_portal_account_id',
        'email',
        'code_hash',
        'expires_at',
        'verified_at',
        'attempt_count',
        'request_ip',
        'sent_at',
    ];

    protected $hidden = [
        'code_hash',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function account()
    {
        return $this->belongsTo(ClientPortalAccount::class, 'client_portal_account_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at === null || $this->expires_at->isPast();
    }
}
