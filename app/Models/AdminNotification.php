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
        'title',
        'message',
        'payload',
        'read_at',
        'dismissed_at',
        'expires_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'read_at' => 'datetime',
        'dismissed_at' => 'datetime',
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
        return $query->whereNull('read_at');
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
}
