<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TopologyTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'topology_data',
        'is_system',
        'created_by',
    ];

    protected $casts = [
        'topology_data' => 'array',
        'is_system' => 'boolean',
    ];

    /**
     * Get the user who created this template.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for system templates.
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope for user-created templates.
     */
    public function scopeUserCreated($query)
    {
        return $query->where('is_system', false);
    }
}
