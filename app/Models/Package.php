<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory, LogsModelActivity;

    protected $fillable = [
        'service_id',
        'hestia_package',
        'name',
        'price',
        'unit',
        'bandwidth_down',
        'bandwidth_up',
        'quota',
        'max_mailboxes',
        'mailbox_quota_mb',
        'alias_max',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'max_mailboxes' => 'integer',
        'mailbox_quota_mb' => 'integer',
        'alias_max' => 'integer',
    ];

    protected string $activitylogEntityName = 'package';

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
