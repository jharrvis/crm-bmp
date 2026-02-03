<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionTopology extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'topology_data',
        'thumbnail',
        'version',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'topology_data' => 'array',
    ];

    /**
     * Get the subscription that owns the topology.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Get the user who created the topology.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the topology.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the version history for this topology.
     */
    public function histories(): HasMany
    {
        return $this->hasMany(SubscriptionTopologyHistory::class)->orderBy('version', 'desc');
    }

    /**
     * Save current state to history before updating.
     */
    public function saveToHistory(?int $userId = null, ?string $summary = null): void
    {
        $this->histories()->create([
            'topology_data' => $this->topology_data,
            'version' => $this->version,
            'changed_by' => $userId ?? $this->updated_by,
            'change_summary' => $summary,
        ]);
    }
}
