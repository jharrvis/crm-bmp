<?php

namespace App\Services;

class DashboardWidgetRegistry
{
    /**
     * Single source of truth untuk widget dashboard.
     * permission = gate @can + server-side stats; route = link CTA.
     * w = col-span (3 stat, 6 chart, 12 full). Tidak resize di Fase ini — hanya show/hide + urutan.
     */
    public const WIDGETS = [
        // P0 Bisnis
        'clients_count' => [
            'title' => 'Total Pelanggan',
            'permission' => 'clients.view',
            'route' => 'clients.index',
            'group' => 'Bisnis',
            'w' => 3,
            'default_roles' => ['Owner', 'Admin', 'Billing', 'NOC', 'CS', 'Sales', 'Finance', 'Employee'],
        ],
        'subscriptions_status' => [
            'title' => 'Langganan per Status',
            'permission' => 'subscriptions.view',
            'route' => 'subscriptions.index',
            'group' => 'Bisnis',
            'w' => 3,
        ],
        'growth' => [
            'title' => 'Pertumbuhan Pelanggan',
            'permission' => 'clients.view',
            'route' => 'clients.index',
            'group' => 'Bisnis',
            'w' => 6,
            'period' => true,
        ],
        'top_packages' => [
            'title' => 'Paket Terlaris',
            'permission' => 'packages.view',
            'route' => 'packages.index',
            'group' => 'Bisnis',
            'w' => 6,
        ],
        // P0 Keuangan
        'outstanding_invoice' => [
            'title' => 'Outstanding Invoice',
            'permission' => 'invoices.view',
            'route' => 'invoices.index',
            'group' => 'Keuangan',
            'w' => 3,
        ],
        'revenue' => [
            'title' => 'Revenue Bulan Ini',
            'permission' => 'financial_reports.view',
            'route' => 'reports.financial.index',
            'group' => 'Keuangan',
            'w' => 3,
            'period' => true,
        ],
        'pending_payments' => [
            'title' => 'Perlu Verifikasi',
            'permission' => 'payments.verify',
            'route' => 'payments.index',
            'group' => 'Keuangan',
            'w' => 3,
        ],
        'due_invoices' => [
            'title' => 'Jatuh Tempo 7 Hari',
            'permission' => 'invoices.view',
            'route' => 'invoices.index',
            'group' => 'Keuangan',
            'w' => 3,
        ],
        // P0 Support
        'tickets_open' => [
            'title' => 'Tiket Terbuka',
            'permission' => 'tickets.view',
            'route' => 'tickets.index',
            'group' => 'Support',
            'w' => 3,
        ],
        'tickets_unresponded' => [
            'title' => 'Belum Respon 24j',
            'permission' => 'tickets.view',
            'route' => 'tickets.index',
            'group' => 'Support',
            'w' => 3,
        ],
        'recent_activity' => [
            'title' => 'Aktivitas Terakhir',
            'permission' => 'logs.view',
            'route' => 'activity-logs.index',
            'group' => 'Sistem',
            'w' => 6,
        ],
        // P1 Infra
        'zabbix_health' => [
            'title' => 'Kesehatan Zabbix',
            'permission' => 'zabbix_monitors.view',
            'route' => 'zabbix-monitors.index',
            'group' => 'Infrastruktur',
            'w' => 6,
        ],
        'router_server' => [
            'title' => 'Router / Server',
            'permission' => 'routers.view', // atau servers.view — gate any
            'route' => 'routers.index',
            'group' => 'Infrastruktur',
            'w' => 3,
        ],
        'domain_expiry' => [
            'title' => 'Domain Expired <30 Hari',
            'permission' => 'domains.view',
            'route' => 'subscriptions.index',
            'group' => 'Infrastruktur',
            'w' => 3,
        ],
        'registrar_health' => [
            'title' => 'Registrar Health',
            'permission' => 'registrar_accounts.view',
            'route' => 'registrar-accounts.index',
            'group' => 'Infrastruktur',
            'w' => 3,
        ],
        // P2 Sistem + Notifikasi
        'notifications_unread' => [
            'title' => 'Notifikasi Belum Dibaca',
            'permission' => 'notifications.view',
            'route' => 'notifications.index',
            'group' => 'Sistem',
            'w' => 3,
        ],
        'notifications_action' => [
            'title' => 'Perlu Tindakan',
            'permission' => 'notifications.view',
            'route' => 'notifications.index',
            'group' => 'Sistem',
            'w' => 3,
        ],
        // Map widget (C)
        'operational_map' => [
            'title' => 'Peta Operasional',
            'permission' => 'maps.view',
            'route' => 'operational-map.index',
            'group' => 'Infrastruktur',
            'w' => 6,
        ],
    ];

    public static function all(): array
    {
        return self::WIDGETS;
    }

    public static function exists(string $id): bool
    {
        return isset(self::WIDGETS[$id]);
    }

    public static function defaultForRole(\App\Models\User $user): array
    {
        // Default per role — sesuai keputusan Fase 0
        $roles = $user->getRoleNames()->toArray();
        $is = fn (array $r) => count(array_intersect($roles, $r)) > 0;

        // Owner: semua
        if ($is(['Owner'])) {
            return self::layout(array_keys(self::WIDGETS));
        }
        if ($is(['Admin'])) {
            return self::layout(array_keys(self::WIDGETS));
        }
        if ($is(['NOC'])) {
            return self::layout(['clients_count', 'subscriptions_status', 'tickets_open', 'tickets_unresponded', 'zabbix_health', 'router_server', 'domain_expiry', 'registrar_health', 'notifications_unread', 'notifications_action', 'operational_map', 'recent_activity']);
        }
        if ($is(['Billing', 'Finance'])) {
            return self::layout(['clients_count', 'outstanding_invoice', 'revenue', 'pending_payments', 'due_invoices', 'notifications_unread', 'notifications_action', 'recent_activity']);
        }
        if ($is(['CS'])) {
            return self::layout(['clients_count', 'subscriptions_status', 'tickets_open', 'tickets_unresponded', 'notifications_unread', 'notifications_action', 'recent_activity']);
        }
        if ($is(['Sales'])) {
            return self::layout(['clients_count', 'top_packages', 'growth', 'notifications_unread', 'recent_activity']);
        }
        // Employee & fallback
        return self::layout(['clients_count', 'subscriptions_status', 'tickets_open', 'notifications_unread', 'recent_activity']);
    }

    private static function layout(array $ids): array
    {
        return array_map(fn ($id) => ['id' => $id, 'visible' => true], $ids);
    }

    public static function visibleForUser(\App\Models\User $user, ?array $preferences): array
    {
        $layout = $preferences['layout'] ?? self::defaultForRole($user);
        // Filter hanya yang masih exists dan visible + user punya permission
        return array_values(array_filter($layout, function ($item) use ($user) {
            $id = $item['id'] ?? null;
            if (! $id || ! self::exists($id) || empty($item['visible'])) {
                return false;
            }
            $perm = self::WIDGETS[$id]['permission'] ?? null;
            if ($perm && ! $user->can($perm)) {
                // Khusus router_server: allow jika punya salah satu
                if ($id === 'router_server' && ($user->can('routers.view') || $user->can('servers.view'))) {
                    return true;
                }
                return false;
            }
            return true;
        }));
    }
}