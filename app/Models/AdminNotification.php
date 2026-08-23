<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'target_role',
        'type',
        'category',
        'severity',
        'action_required',
        'action_key',
        'source_type',
        'source_id',
        'dedupe_key',
        'title',
        'message',
        'payload',
        'read_at',
        'dismissed_at',
        'resolved_at',
        'resolved_by',
        'snoozed_until',
        'expires_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'action_required' => 'boolean',
        'read_at' => 'datetime',
        'dismissed_at' => 'datetime',
        'resolved_at' => 'datetime',
        'snoozed_until' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at')->whereNull('dismissed_at')->whereNull('resolved_at')->where(function ($q) {
            $q->whereNull('snoozed_until')->orWhere('snoozed_until', '<=', now());
        });
    }

    public function scopeActionRequired($query)
    {
        return $query->where('action_required', true)->whereNull('resolved_at')->whereNull('dismissed_at')->where(function ($q) {
            $q->whereNull('snoozed_until')->orWhere('snoozed_until', '<=', now());
        });
    }

    public function isResolved(): bool
    {
        return $this->resolved_at !== null;
    }

    public function isSnoozed(): bool
    {
        return $this->snoozed_until !== null && $this->snoozed_until->isFuture();
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhere(function ($q2) use ($user) {
                    $q2->whereNull('user_id')
                        ->whereIn('target_role', $user->getRoleNames()->toArray());
                })
                ->orWhere(function ($q3) {
                    $q3->whereNull('user_id')->whereNull('target_role');
                });
        });
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
