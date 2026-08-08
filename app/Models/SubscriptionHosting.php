<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionHosting extends Model
{
    use HasFactory, LogsModelActivity;

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
        'provisioning_status',
        'provisioning_error',
        'provisioned_at',
        'remote_user_created_at',
        'delete_requested_at',
        'managed_by_crm',
        'suspended_by_subscription',
        'hestia_package',
    ];

    protected $casts = [
        'ssl_expiry' => 'date',
        'password_encrypted' => 'encrypted',
        'managed_by_crm' => 'boolean',
        'suspended_by_subscription' => 'boolean',
        'provisioned_at' => 'datetime',
        'remote_user_created_at' => 'datetime',
        'delete_requested_at' => 'datetime',
    ];

    protected $hidden = [
        'password_encrypted',
    ];

    protected array $activitylogExcludeAttributes = [
        'password_encrypted',
    ];

    protected string $activitylogEntityName = 'hosting subscription';

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function hostingServer()
    {
        return $this->belongsTo(HostingServer::class);
    }
}
