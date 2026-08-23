<?php

namespace App\Services\Admin;

use App\Models\AdminNotification;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class AdminNotificationService
{
    /**
     * Generic notify — registry-aware.
     * Jika type ada di NotificationTypeRegistry, category/severity/action_required/action_key/ttl akan diisi otomatis.
     * Mendukung source_type/source_id/dedupe_key untuk dedupe generik.
     */
    public function notify(
        string $type,
        string $title,
        string $message,
        array $payload = [],
        ?int $userId = null,
        ?string $targetRole = null,
        ?int $expiresDays = null,
        ?string $sourceType = null,
        mixed $sourceId = null,
        ?string $dedupeState = null,
        ?string $category = null,
        ?string $severity = null,
        ?bool $actionRequired = null,
        ?string $actionKey = null,
    ): AdminNotification {
        $meta = NotificationTypeRegistry::get($type);
        $category = $category ?? $meta['category'] ?? null;
        $severity = $severity ?? $meta['severity'] ?? null;
        $actionRequired = $actionRequired ?? $meta['action_required'] ?? false;
        $actionKey = $actionKey ?? $meta['action_key'] ?? null;
        $expiresDays = $expiresDays ?? $meta['ttl_days'] ?? null;
        $expiresAt = $expiresDays ? now()->addDays($expiresDays) : ($payload['expires_at'] ?? null);

        // Redaksi payload — jangan simpan secret
        $payload = $this->redactPayload($payload);

        // Build dedupe_key
        $dedupeKey = null;
        $dedupeMode = $meta['dedupe'] ?? null;
        if ($dedupeMode !== null || $sourceType !== null || $sourceId !== null) {
            $dedupeKey = NotificationTypeRegistry::dedupeKey($type, $sourceType, $sourceId, $dedupeState);
        } elseif (isset($payload['domain_name']) && isset($payload['days_left'])) {
            // Fallback legacy domain
            $dedupeKey = NotificationTypeRegistry::dedupeKey($type, null, null, $payload['domain_name'].':'.$payload['days_left']);
        }

        // P1 dedupe: Cache::lock untuk cegah race condition — block 5 detik, create hanya di dalam lock
        $lockKey = 'admin_notifications:dedupe:'.($dedupeKey ?? sha1($type.':'.($payload['domain_name'] ?? '').':'.($payload['days_left'] ?? ''))).':'.($userId ?? $targetRole ?? 'global');
        $lock = Cache::lock($lockKey, 10);

        try {
            return $lock->block(5, function () use ($type, $title, $message, $payload, $userId, $targetRole, $category, $severity, $actionRequired, $actionKey, $sourceType, $sourceId, $dedupeKey, $dedupeMode, $expiresAt) {
                // Dedupe check — hanya di dalam lock
                if ($dedupeKey !== null) {
                    $query = AdminNotification::where('dedupe_key', $dedupeKey);
                    if ($dedupeMode === 'incident') {
                        $query->whereNull('resolved_at');
                    } else {
                        $query->whereDate('created_at', now()->startOfDay());
                    }
                    if ($userId !== null) {
                        $query->where('user_id', $userId);
                    } elseif ($targetRole !== null) {
                        $query->where('target_role', $targetRole);
                    }
                    if ($query->exists()) {
                        return $query->latest('id')->first();
                    }
                } elseif (isset($payload['domain_name']) && isset($payload['days_left'])) {
                    $today = now()->startOfDay();
                    $domain = $payload['domain_name'];
                    $days = $payload['days_left'];
                    $q = AdminNotification::where('type', $type)->whereDate('created_at', $today)
                        ->whereJsonContains('payload->domain_name', $domain)
                        ->whereJsonContains('payload->days_left', $days);
                    if ($userId !== null) {
                        $q->where('user_id', $userId);
                    } elseif ($targetRole !== null) {
                        $q->where('target_role', $targetRole);
                    }
                    if ($q->exists()) {
                        return $q->latest('id')->first();
                    }
                }

                return AdminNotification::create([
                    'user_id' => $userId,
                    'target_role' => $targetRole,
                    'type' => $type,
                    'category' => $category,
                    'severity' => $severity,
                    'action_required' => $actionRequired,
                    'action_key' => $actionKey,
                    'source_type' => $sourceType,
                    'source_id' => $sourceId,
                    'dedupe_key' => $dedupeKey,
                    'title' => $title,
                    'message' => $message,
                    'payload' => $payload,
                    'expires_at' => $expiresAt,
                ]);
            });
        } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
            // Gagal dapat lock dalam 5 detik — cek existing dedupe, jangan buat duplikat
            if ($dedupeKey !== null) {
                $q = AdminNotification::where('dedupe_key', $dedupeKey);
                if ($dedupeMode === 'incident') {
                    $q->whereNull('resolved_at');
                } else {
                    $q->whereDate('created_at', now()->startOfDay());
                }
                if ($userId !== null) {
                    $q->where('user_id', $userId);
                } elseif ($targetRole !== null) {
                    $q->where('target_role', $targetRole);
                }
                if ($q->exists()) {
                    return $q->latest('id')->first();
                }
            }
            // Re-throw agar job retry, bukan silent duplicate
            throw $e;
        }
    }

    /**
     * Notify via source model — helper paling generik.
     */
    public function notifyForSource(string $type, $source, array $context = [], ?string $dedupeState = null): void
    {
        $meta = NotificationTypeRegistry::get($type);
        if (! $meta) {
            return;
        }
        $sourceType = $source ? get_class($source) : null;
        $sourceId = $source?->getKey();
        $payload = array_merge($context, [
            'source_type' => $sourceType,
            'source_id' => $sourceId,
        ]);
        // Populate common fields jika ada di source
        if ($source) {
            if (isset($source->domain_name)) {
                $payload['domain_name'] = $source->domain_name;
            }
            if (isset($source->invoice_number)) {
                $payload['invoice_number'] = $source->invoice_number;
            }
            if (isset($source->ticket_number)) {
                $payload['ticket_number'] = $source->ticket_number;
            }
        }

        $audienceRoles = $meta['audience'] ?? ['Owner', 'Admin'];
        $title = $context['title'] ?? $this->defaultTitle($type, $context);
        $message = $context['message'] ?? $this->defaultMessage($type, $context);

        $this->notifyRolesViaRegistry($type, $title, $message, $payload, $audienceRoles, $sourceType, $sourceId, $dedupeState);
    }

    private function notifyRolesViaRegistry(string $type, string $title, string $message, array $payload, array $roles, ?string $sourceType, mixed $sourceId, ?string $dedupeState): void
    {
        $users = User::role($roles)->get();
        if ($users->isEmpty()) {
            // Fallback broadcast jika seed belum
            $meta = NotificationTypeRegistry::get($type);
            $this->notify($type, $title, $message, $payload, null, $roles[0] ?? 'Admin', null, $sourceType, $sourceId, $dedupeState);
            return;
        }
        foreach ($users as $user) {
            $this->notify($type, $title, $message, $payload, $user->id, null, null, $sourceType, $sourceId, $dedupeState);
        }
        foreach ($users as $user) {
            Cache::forget('admin_notifications:unread:'.$user->id);
        }
    }

    private function defaultTitle(string $type, array $ctx): string
    {
        return match ($type) {
            'invoice_overdue' => 'Tagihan jatuh tempo',
            'payment_verification' => 'Pembayaran perlu verifikasi',
            'ticket_unassigned' => 'Tiket belum ditugaskan',
            'domain_expiry' => 'Domain akan expired',
            default => str_replace('_', ' ', ucfirst($type)),
        };
    }

    private function defaultMessage(string $type, array $ctx): string
    {
        return $ctx['message'] ?? $type;
    }

    private function redactPayload(array $payload): array
    {
        $forbiddenExact = ['auth_code', 'auth_code_encrypted', 'password', 'secret', 'api_key', 'token', 'provider_metadata', 'identity_number', 'notes', 'auth_code_encrypted', 'secret_encrypted', 'api_key_encrypted', 'token_encrypted'];
        $forbiddenSubstr = ['password', 'secret', 'auth_code', 'api_key', 'token'];

        foreach ($payload as $key => $value) {
            $lower = strtolower((string) $key);
            $isForbidden = in_array($lower, $forbiddenExact, true) || in_array($key, $forbiddenExact, true);
            if (! $isForbidden) {
                foreach ($forbiddenSubstr as $sub) {
                    if (str_contains($lower, $sub)) {
                        $isForbidden = true;
                        break;
                    }
                }
            }
            if ($isForbidden) {
                unset($payload[$key]);
                continue;
            }
            // Recurse untuk nested array/object apapun (metadata, context, details, payload, dll)
            if (is_array($value)) {
                $payload[$key] = $this->redactPayload($value);
            }
        }
        return $payload;
    }

    /**
     * P1-3 + P1-4: Broadcast per-user ke Owner dan Admin (bukan satu record global).
     * Setiap Admin/Owner mendapat record sendiri sehingga read_at per user.
     * Wrapper compatibility — delegasikan ke notify generik.
     * FIX P1: terusan sourceType/sourceId dengan benar agar dedupe_key tidak jadi type:::
     */
    public function notifyAdmins(string $type, string $title, string $message, array $payload = []): void
    {
        $meta = NotificationTypeRegistry::get($type);
        $sourceType = $payload['source_type'] ?? null;
        $sourceId = $payload['source_id'] ?? $payload['subscription_id'] ?? $payload['invoice_id'] ?? $payload['subscription_domain_id'] ?? $payload['ticket_id'] ?? null;
        $dedupeState = $payload['days_left'] ?? $payload['dedupe_state'] ?? null;
        if ($dedupeState === null && isset($payload['domain_name']) && $meta && ($meta['dedupe'] ?? null) === 'daily') {
            // daily dedupe fallback via domain_name jika days_left tidak ada
            $dedupeState = $payload['domain_name'];
        }

        if ($meta) {
            $audience = $meta['audience'] ?? ['Owner', 'Admin'];
            $this->notifyRolesViaRegistry($type, $title, $message, $payload, $audience, $sourceType, $sourceId, $dedupeState !== null ? (string) $dedupeState : null);
            return;
        }

        $users = User::role(['Owner', 'Admin'])->get();
        if ($users->isEmpty()) {
            $this->notify($type, $title, $message, $payload, null, 'Admin');
            return;
        }

        foreach ($users as $user) {
            $this->notify($type, $title, $message, $payload, $user->id, null, null, $sourceType, $sourceId, $dedupeState !== null ? (string) $dedupeState : null);
        }

        foreach ($users as $user) {
            Cache::forget('admin_notifications:unread:'.$user->id);
        }
    }

    /**
     * Helper untuk notifikasi yang butuh Owner saja atau Billing dll — tetap per-user.
     */
    public function notifyRoles(array $roles, string $type, string $title, string $message, array $payload = []): void
    {
        $meta = NotificationTypeRegistry::get($type);
        $sourceType = $payload['source_type'] ?? null;
        $sourceId = $payload['source_id'] ?? $payload['subscription_id'] ?? $payload['invoice_id'] ?? $payload['ticket_id'] ?? null;
        $dedupeState = $payload['days_left'] ?? $payload['dedupe_state'] ?? null;

        if ($meta) {
            $this->notifyRolesViaRegistry($type, $title, $message, $payload, $roles, $sourceType, $sourceId, $dedupeState !== null ? (string) $dedupeState : null);
            return;
        }
        $users = User::role($roles)->get();
        foreach ($users as $user) {
            $this->notify($type, $title, $message, $payload, $user->id, null, null, $sourceType, $sourceId, $dedupeState !== null ? (string) $dedupeState : null);
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

    public function markResolved(AdminNotification $notification, ?User $user = null): void
    {
        if ($notification->resolved_at === null) {
            $notification->update([
                'resolved_at' => now(),
                'resolved_by' => $user?->id ?? auth()->id(),
                'read_at' => $notification->read_at ?? now(),
            ]);
            if ($notification->user_id) {
                Cache::forget('admin_notifications:unread:'.$notification->user_id);
            }
        }
    }

    public function snooze(AdminNotification $notification, int $hours = 24): void
    {
        $notification->update(['snoozed_until' => now()->addHours($hours)]);
        if ($notification->user_id) {
            Cache::forget('admin_notifications:unread:'.$notification->user_id);
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
        return Cache::remember($key, 120, fn () => AdminNotification::forUser($user)->unread()->count());
    }

    public function actionRequiredCountForUser(User $user): int
    {
        $key = 'admin_notifications:action_required:'.$user->id;
        return Cache::remember($key, 120, fn () => AdminNotification::forUser($user)->actionRequired()->count());
    }
}