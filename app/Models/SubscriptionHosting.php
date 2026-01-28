<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionHosting extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'hosting_server_id',
        'domain',
        'username',
        'password_encrypted',
        'disk_quota_gb',
        'email_accounts',
        'databases',
        'ssl_expiry',
    ];

    protected $casts = [
        'ssl_expiry' => 'date',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function hostingServer()
    {
        return $this->belongsTo(HostingServer::class);
    }
}
