<?php

namespace App\Services\Admin;

class NotificationTypeRegistry
{
    /**
     * Single source of truth untuk semua tipe notifikasi admin.
     *
     * Struk: type => [category, severity, action_required, permission, action_key, action_label, dashboard, ttl_days, dedupe, audience]
     * - type generik (domain_expiry bukan domain_expiry_7) — variasi hari di payload/dedupe
     * - permission: gate CTA sebelum render
     * - dashboard: apakah boleh masuk widget notifikasi
     * - dedupe: daily | incident | none
     */
    public const TYPES = [
        'domain_expiry' => [
            'category' => 'domain',
            'severity' => 'warning',
            'action_required' => true,
            'permission' => 'domains.view',
            'action_key' => 'view_domain',
            'action_label' => 'Lihat Domain',
            'dashboard' => true,
            'ttl_days' => 30,
            'dedupe' => 'daily',
            'audience' => ['Owner', 'Admin'],
        ],
        'domain_overdue' => [
            'category' => 'domain',
            'severity' => 'critical',
            'action_required' => true,
            'permission' => 'domains.renew',
            'action_key' => 'request_renew',
            'action_label' => 'Ajukan Renew',
            'dashboard' => true,
            'ttl_days' => 30,
            'dedupe' => 'incident',
            'audience' => ['Owner', 'Admin'],
        ],
        'domain_sync_failed' => [
            'category' => 'domain',
            'severity' => 'high',
            'action_required' => true,
            'permission' => 'registrar_accounts.view',
            'action_key' => 'view_registrar_error',
            'action_label' => 'Lihat Error',
            'dashboard' => true,
            'ttl_days' => 14,
            'dedupe' => 'incident',
            'audience' => ['Owner', 'Admin', 'NOC'],
        ],
        'domain_conflict' => [
            'category' => 'domain',
            'severity' => 'high',
            'action_required' => true,
            'permission' => 'registrar_accounts.view',
            'action_key' => 'resolve_domain_conflict',
            'action_label' => 'Resolve Konflik',
            'dashboard' => true,
            'ttl_days' => 14,
            'dedupe' => 'incident',
            'audience' => ['Owner', 'Admin'],
        ],
        'registrar_offline' => [
            'category' => 'infrastructure',
            'severity' => 'critical',
            'action_required' => true,
            'permission' => 'registrar_accounts.view',
            'action_key' => 'test_registrar_connection',
            'action_label' => 'Test Koneksi',
            'dashboard' => true,
            'ttl_days' => 7,
            'dedupe' => 'incident',
            'audience' => ['Owner', 'Admin', 'NOC'],
        ],
        'hosting_ssl_expiry' => [
            'category' => 'hosting',
            'severity' => 'warning',
            'action_required' => true,
            'permission' => 'subscriptions.view',
            'action_key' => 'view_hosting',
            'action_label' => 'Lihat Hosting',
            'dashboard' => true,
            'ttl_days' => 14,
            'dedupe' => 'daily',
            'audience' => ['Owner', 'Admin', 'NOC'],
        ],
        'hosting_provision_failed' => [
            'category' => 'hosting',
            'severity' => 'high',
            'action_required' => true,
            'permission' => 'servers.view',
            'action_key' => 'view_hosting_log',
            'action_label' => 'Lihat Log',
            'dashboard' => true,
            'ttl_days' => 14,
            'dedupe' => 'incident',
            'audience' => ['Owner', 'Admin', 'NOC'],
        ],
        'invoice_overdue' => [
            'category' => 'billing',
            'severity' => 'high',
            'action_required' => true,
            'permission' => 'invoices.view',
            'action_key' => 'view_invoice',
            'action_label' => 'Lihat Tagihan',
            'dashboard' => true,
            'ttl_days' => 30,
            'dedupe' => 'daily',
            'audience' => ['Owner', 'Admin', 'Billing', 'Finance'],
        ],
        'payment_verification' => [
            'category' => 'billing',
            'severity' => 'high',
            'action_required' => true,
            'permission' => 'payments.verify',
            'action_key' => 'verify_payment',
            'action_label' => 'Verifikasi Pembayaran',
            'dashboard' => true,
            'ttl_days' => 14,
            'dedupe' => 'incident',
            'audience' => ['Owner', 'Admin', 'Billing', 'Finance'],
        ],
        'ticket_unassigned' => [
            'category' => 'ticket',
            'severity' => 'high',
            'action_required' => true,
            'permission' => 'tickets.assign',
            'action_key' => 'assign_ticket',
            'action_label' => 'Tugaskan Tiket',
            'dashboard' => true,
            'ttl_days' => 14,
            'dedupe' => 'incident',
            'audience' => ['Owner', 'Admin', 'NOC', 'CS'],
        ],
        'ticket_high_priority' => [
            'category' => 'ticket',
            'severity' => 'critical',
            'action_required' => true,
            'permission' => 'tickets.view',
            'action_key' => 'view_ticket',
            'action_label' => 'Lihat Tiket',
            'dashboard' => true,
            'ttl_days' => 7,
            'dedupe' => 'incident',
            'audience' => ['Owner', 'Admin', 'NOC', 'CS'],
        ],
        'system_update_available' => [
            'category' => 'system',
            'severity' => 'info',
            'action_required' => false,
            'permission' => 'system_updates.view',
            'action_key' => 'view_system_update',
            'action_label' => 'Lihat Pembaruan',
            'dashboard' => false,
            'ttl_days' => 14,
            'dedupe' => 'incident',
            'audience' => ['Owner', 'Admin'],
        ],
        'approval_requested' => [
            'category' => 'approval',
            'severity' => 'warning',
            'action_required' => true,
            'permission' => 'domains.approve_renew',
            'action_key' => 'approve_domain_renew',
            'action_label' => 'Setujui Renew',
            'dashboard' => true,
            'ttl_days' => 14,
            'dedupe' => 'incident',
            'audience' => ['Owner', 'Admin'],
        ],
    ];

    public static function get(string $type): ?array
    {
        return self::TYPES[$type] ?? null;
    }

    public static function all(): array
    {
        return self::TYPES;
    }

    public static function exists(string $type): bool
    {
        return isset(self::TYPES[$type]);
    }

    public static function dashboardTypes(): array
    {
        return array_filter(self::TYPES, fn ($def) => ! empty($def['dashboard']));
    }

    public static function categories(): array
    {
        return array_values(array_unique(array_column(self::TYPES, 'category')));
    }

    public static function severities(): array
    {
        return ['info', 'warning', 'high', 'critical'];
    }

    /**
     * Build dedupe_key generik: SHA1(type:source_type:source_id:state).
     * state = days_left untuk expiry harian, error_code untuk offline,
     * kosong untuk overdue single-incident.
     */
    public static function dedupeKey(string $type, ?string $sourceType, mixed $sourceId, ?string $state = null): string
    {
        $raw = implode(':', [$type, $sourceType ?? '', (string) ($sourceId ?? ''), $state ?? '']);
        return substr(sha1($raw), 0, 32);
    }

    /**
     * Resolve CTA server-side — jangan percaya URL dari payload.
     * Return null jika permission tidak terpenuhi atau source tidak ada.
     */
    public static function resolveAction(string $actionKey, array $payload, ?\App\Models\User $user = null): ?array
    {
        $user = $user ?? auth()->user();

        return match ($actionKey) {
            'view_domain' => self::actionIfCan($user, 'domains.view', fn () => self::domainRoute($payload)),
            'request_renew' => self::actionIfCan($user, 'domains.renew', fn () => self::domainRoute($payload)),
            'view_registrar_error' => self::actionIfCan($user, 'registrar_accounts.view', fn () => self::registrarRoute($payload)),
            'resolve_domain_conflict' => self::actionIfCan($user, 'registrar_accounts.view', fn () => self::registrarRoute($payload)),
            'test_registrar_connection' => self::actionIfCan($user, 'registrar_accounts.view', fn () => self::registrarRoute($payload)),
            'view_hosting' => self::actionIfCan($user, 'subscriptions.view', fn () => self::subscriptionRoute($payload)),
            'view_hosting_log' => self::actionIfCan($user, 'servers.view', fn () => self::subscriptionRoute($payload)),
            'view_invoice' => self::actionIfCan($user, 'invoices.view', fn () => self::invoiceRoute($payload)),
            'verify_payment' => self::actionIfCan($user, 'payments.verify', fn () => self::paymentRoute($payload)),
            'assign_ticket' => self::actionIfCan($user, 'tickets.assign', fn () => self::ticketRoute($payload)),
            'view_ticket' => self::actionIfCan($user, 'tickets.view', fn () => self::ticketRoute($payload)),
            'view_system_update' => self::actionIfCan($user, 'system_updates.view', fn () => ['url' => route('system-updates.index'), 'label' => 'Lihat Pembaruan']),
            'approve_domain_renew' => self::actionIfCan($user, 'domains.approve_renew', fn () => self::subscriptionRoute($payload)),
            default => null,
        };
    }

    private static function actionIfCan(?\App\Models\User $user, string $permission, \Closure $resolver): ?array
    {
        if ($user && ! $user->can($permission)) {
            return null;
        }
        try {
            return $resolver();
        } catch (\Throwable) {
            return null;
        }
    }

    private static function domainRoute(array $payload): ?array
    {
        $subId = $payload['subscription_id'] ?? null;
        if (! $subId) {
            return null;
        }
        return ['url' => route('subscriptions.show', $subId), 'label' => 'Lihat Layanan'];
    }

    private static function registrarRoute(array $payload): ?array
    {
        $accId = $payload['registrar_account_id'] ?? null;
        if (! $accId) {
            return null;
        }
        return ['url' => route('registrar-accounts.show', $accId), 'label' => 'Lihat Registrar'];
    }

    private static function subscriptionRoute(array $payload): ?array
    {
        $subId = $payload['subscription_id'] ?? null;
        if (! $subId) {
            return null;
        }
        return ['url' => route('subscriptions.show', $subId), 'label' => 'Lihat Layanan'];
    }

    private static function invoiceRoute(array $payload): ?array
    {
        $invId = $payload['invoice_id'] ?? $payload['source_id'] ?? null;
        if (! $invId) {
            return null;
        }
        return ['url' => route('invoices.show', $invId), 'label' => 'Lihat Tagihan'];
    }

    private static function paymentRoute(array $payload): ?array
    {
        $invId = $payload['invoice_id'] ?? null;
        if (! $invId) {
            return null;
        }
        return ['url' => route('invoices.show', $invId), 'label' => 'Verifikasi Pembayaran'];
    }

    private static function ticketRoute(array $payload): ?array
    {
        $ticketId = $payload['ticket_id'] ?? $payload['source_id'] ?? null;
        if (! $ticketId) {
            return null;
        }
        return ['url' => route('tickets.show', $ticketId), 'label' => 'Lihat Tiket'];
    }
}