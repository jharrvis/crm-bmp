<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InternetBackup extends Model
{
    use HasFactory, LogsModelActivity, SoftDeletes;

    public const STATUS_OPTIONS = [
        'planned' => 'Planned',
        'active' => 'Active',
        'suspended' => 'Suspended',
        'terminated' => 'Terminated',
    ];

    protected $fillable = [
        'vendor_id',
        'subscription_id',
        'name',
        'circuit_id',
        'ip_address',
        'gateway',
        'bandwidth_mbps',
        'monthly_cost',
        'active_date',
        'address',
        'status',
        'notes',
    ];

    protected $casts = [
        'bandwidth_mbps' => 'integer',
        'monthly_cost' => 'decimal:2',
        'active_date' => 'date',
    ];

    protected string $activitylogEntityName = 'internet backup';

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_OPTIONS[$this->status] ?? $this->status;
    }
}
