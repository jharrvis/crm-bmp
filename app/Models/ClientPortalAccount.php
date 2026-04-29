<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientPortalAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'email',
        'status',
        'email_verified_at',
        'last_login_at',
        'last_login_ip',
        'notes',
        'created_by',
        'updated_by',
        'remember_token',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function otpCodes()
    {
        return $this->hasMany(ClientPortalOtp::class);
    }

    public function sessions()
    {
        return $this->hasMany(ClientPortalSession::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
