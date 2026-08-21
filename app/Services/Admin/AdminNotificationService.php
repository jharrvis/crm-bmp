<?php

namespace App\Services\Admin;

use App\Models\AdminNotification;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class AdminNotificationService
{
    /**
     * Create a notification for a specific user or single broadcast.
     * Untuk broadcast yang benar per-user, gunakan notifyAdmins() yang loop per user.
     */
    public function notify(
        string $type,
        string $title,
        string $message,
        array $payload = [],
        ?int $userId = null,
        ?string $targetRole = null,
        ?int $expiresDays = null,
    ): AdminNotification {
        $expiresAt = $expiresDays
            ? now()->addDays($expiresDays)
            : ($payload['expires_at'] ?? null);

        $today = now()->startOfDay();
        $domain = $payload['domain_name'] ?? null;
        $days = $payload['days_left'] ?? null;

        // P1-3: Dedupe per-user, bukan global
        if ($domain !== null && $days !== null) {
            $query = AdminNotification::where('type', $type)
                ->whereDate('created_at', $today)
                ->whereJsonContains('payload->domain_name', $domain)
                ->whereJsonContains('payload->days_left', $days);
            if ($userId !== null) {
                $query->where('user_id', $userId);
            } elseif ($targetRole !== null) {
                $query->where('target_role', $targetRole);
            }
            if ($query->exists()) {
                return $query->latest('id')->first();
            }
        }

        return AdminNotification::create([
            'user_id' => $userId,
            'target_role' => $targetRole,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'payload' => $payload,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * P1-3 + P1-4: Broadcast per-user ke Owner dan Admin (bukan satu record global).
     * Setiap Admin/Owner mendapat record sendiri sehingga read_at per user.
     */
    public function notifyAdmins(string $type, string $title, string $message, array $payload = []): void
    {
        $users = User::role(['Owner', 'Admin'])->get();
        // Fallback jika belum ada role (seed belum jalan) — buat satu broadcast
        if ($users->isEmpty()) {
            $this->notify($type, $title, $message, $payload, null, 'Admin');
            return;
        }

        foreach ($users as $user) {
            $this->notify($type, $title, $message, $payload, $user->id, null);
        }

        // Invalidate cache per user
        foreach ($users as $user) {
            Cache::forget('admin_notifications:unread:'.$user->id);
        }
    }

    /**
     * Helper untuk notifikasi yang butuh Owner saja atau Billing dll — tetap per-user.
     */
    public function notifyRoles(array $roles, string $type, string $title, string $message, array $payload = []): void
    {
        $users = User::role($roles)->get();
        foreach ($users as $user) {
            $this->notify($type, $title, $message, $payload, $user->id, null);
        }
        foreach ($users as $user) {
            Cache::forget('admin_notifications:unread:'.$user->id);
        }
    }

    public function markRead(AdminNotification $notification, ?User $user = null): void
    {
        if ($notification->read_at === null) {
            $notification->update(['read_at' => now()]);
            if ($notification->user_id) {
                Cache::forget('admin_notifications:unread:'.$notification->user_id);
            }
        }
    }

    public function markAllReadForUser(User $user): int
    {
        $count = AdminNotification::forUser($user)->whereNull('read_at')->update(['read_at' => now()]);
        Cache::forget('admin_notifications:unread:'.$user->id);
        return $count;
    }

    public function pruneExpired(int $retentionDays = 90): int
    {
        $cutoff = now()->subDays($retentionDays);
        return AdminNotification::where('created_at', '<', $cutoff)->delete();
    }

    public function unreadCountForUser(User $user): int
    {
        $key = 'admin_notifications:unread:'.$user->id;
        return Cache::remember($key, 120, fn () => AdminNotification::forUser($user)->whereNull('read_at')->whereNull('dismissed_at')->count());
    }
}
