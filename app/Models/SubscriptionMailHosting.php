<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionMailHosting extends Model
{
    use HasFactory, LogsModelActivity;

    protected $fillable = [
        'subscription_id',
        'mail_server_id',
        'domain',
        'admin_email',
        'admin_password_encrypted',
        'max_mailboxes',
        'mailbox_quota_mb',
        'alias_max',
        'mail_server_type',
        'status',
        'provisioning_status',
        'provisioning_error',
        'provisioned_at',
    ];

    protected $casts = [
        'max_mailboxes' => 'integer',
        'mailbox_quota_mb' => 'integer',
        'alias_max' => 'integer',
        'admin_password_encrypted' => 'encrypted',
        'provisioned_at' => 'datetime',
    ];

    protected $hidden = [
        'admin_password_encrypted',
    ];

    protected array $activitylogExcludeAttributes = [
        'admin_password_encrypted',
    ];

    protected string $activitylogEntityName = 'mail hosting';

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function mailServer()
    {
        return $this->belongsTo(HostingServer::class, 'mail_server_id');
    }

    public function mailboxes()
    {
        return $this->hasMany(Mailbox::class, 'subscription_mail_hosting_id');
    }
}
