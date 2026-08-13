<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mailbox extends Model
{
    use HasFactory, LogsModelActivity;

    protected $fillable = [
        'subscription_mail_hosting_id',
        'email',
        'zimbra_id',
        'display_name',
        'password_encrypted',
        'quota_mb',
        'used_quota_mb',
        'alias_count',
        'is_active',
        'managed_by_crm',
        'remote_status',
        'suspended_by_subscription',
        'provisioning_status',
        'provisioning_error',
        'provisioned_at',
    ];

    protected $casts = [
        'password_encrypted' => 'encrypted',
        'quota_mb' => 'integer',
        'used_quota_mb' => 'integer',
        'alias_count' => 'integer',
        'is_active' => 'boolean',
        'managed_by_crm' => 'boolean',
        'suspended_by_subscription' => 'boolean',
        'provisioned_at' => 'datetime',
    ];

    protected $hidden = [
        'password_encrypted',
    ];

    protected array $activitylogExcludeAttributes = [
        'password_encrypted',
    ];

    protected string $activitylogEntityName = 'mailbox';

    public function mailHosting()
    {
        return $this->belongsTo(SubscriptionMailHosting::class, 'subscription_mail_hosting_id');
    }
}
