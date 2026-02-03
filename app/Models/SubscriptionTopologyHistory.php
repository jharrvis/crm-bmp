<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionTopologyHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'subscription_topology_id',
        'topology_data',
        'version',
        'changed_by',
        'change_summary',
    ];

    protected $casts = [
        'topology_data' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the topology this history belongs to.
     */
    public function topology(): BelongsTo
    {
        return $this->belongsTo(SubscriptionTopology::class, 'subscription_topology_id');
    }

    /**
     * Get the user who made this change.
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
