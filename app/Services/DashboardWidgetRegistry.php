<?php

namespace App\Services;

class DashboardWidgetRegistry
{
    public const PRESET_W = [3, 4, 6, 8, 12];

    /**
     * Single source of truth untuk widget dashboard.
     * w kini disimpan sebagai default_w/min_w/max_w berbasis grid 12 kolom.
     * Posisi = urutan array layout; ukuran = w preset yang di-clamp server-side.
     */
    public const WIDGETS = [
        // P0 Bisnis — stat ringkas 3 (min 3 max 4), chart/list 6
        'clients_count' => [
            'title' => 'Total Pelanggan',
            'permission' => 'clients.view',
            'route' => 'clients.index',
            'group' => 'Bisnis',
            'default_w' => 3, 'min_w' => 3, 'max_w' => 4,
            'w' => 3, // legacy compat
            'default_roles' => ['Owner', 'Admin', 'Billing', 'NOC', 'CS', 'Sales', 'Finance', 'Employee'],
        ],
        'subscriptions_status' => [
            'title' => 'Langganan per Status',
            'permission' => 'subscriptions.view',
            'route' => 'subscriptions.index',
            'group' => 'Bisnis',
            'default_w' => 3, 'min_w' => 3, 'max_w' => 4,
            'w' => 3,
        ],
        'growth' => [
            'title' => 'Pertumbuhan Pelanggan',
            'permission' => 'clients.view',
            'route' => 'clients.index',
            'group' => 'Bisnis',
            'default_w' => 6, 'min_w' => 6, 'max_w' => 12,
            'w' => 6,
            'period' => true,
        ],
        'top_packages' => [
            'title' => 'Paket Terlaris',
            'permission' => 'packages.view',
            'route' => 'packages.index',
            'group' => 'Bisnis',
            'default_w' => 6, 'min_w' => 4, 'max_w' => 8,
            'w' => 6,
        ],
        // P0 Keuangan — stat 3
        'outstanding_invoice' => [
            'title' => 'Outstanding Invoice',
            'permission' => 'invoices.view',
            'route' => 'invoices.index',
            'group' => 'Keuangan',
            'default_w' => 3, 'min_w' => 3, 'max_w' => 4,
            'w' => 3,
        ],
        'revenue' => [
            'title' => 'Revenue Bulan Ini',
            'permission' => 'financial_reports.view',
            'route' => 'reports.financial.index',
            'group' => 'Keuangan',
            'default_w' => 3, 'min_w' => 3, 'max_w' => 4,
            'w' => 3,
            'period' => true,
        ],
        'pending_payments' => [
            'title' => 'Perlu Verifikasi',
            'permission' => 'payments.verify',
            'route' => 'payments.index',
            'group' => 'Keuangan',
            'default_w' => 3, 'min_w' => 3, 'max_w' => 4,
            'w' => 3,
        ],
        'due_invoices' => [
            'title' => 'Jatuh Tempo 7 Hari',
            'permission' => 'invoices.view',
            'route' => 'invoices.index',
            'group' => 'Keuangan',
            'default_w' => 3, 'min_w' => 3, 'max_w' => 4,
            'w' => 3,
        ],
        // P0 Support
        'tickets_open' => [
            'title' => 'Tiket Terbuka',
            'permission' => 'tickets.view',
            'route' => 'tickets.index',
            'group' => 'Support',
            'default_w' => 3, 'min_w' => 3, 'max_w' => 4,
            'w' => 3,
        ],
        'tickets_unresponded' => [
            'title' => 'Belum Respon 24j',
            'permission' => 'tickets.view',
            'route' => 'tickets.index',
            'group' => 'Support',
            'default_w' => 3, 'min_w' => 3, 'max_w' => 4,
            'w' => 3,
        ],
        'recent_activity' => [
            'title' => 'Aktivitas Terakhir',
            'permission' => 'logs.view',
            'route' => 'activity-logs.index',
            'group' => 'Sistem',
            'default_w' => 6, 'min_w' => 6, 'max_w' => 12,
            'w' => 6,
        ],
        // P1 Infra
        'zabbix_health' => [
            'title' => 'Kesehatan Zabbix',
            'permission' => 'zabbix_monitors.view',
            'route' => 'zabbix-monitors.index',
            'group' => 'Infrastruktur',
            'default_w' => 6, 'min_w' => 6, 'max_w' => 12,
            'w' => 6,
        ],
        'router_server' => [
            'title' => 'Router / Server',
            'permission' => 'routers.view', // atau servers.view — gate any
            'route' => 'routers.index',
            'group' => 'Infrastruktur',
            'default_w' => 3, 'min_w' => 3, 'max_w' => 4,
            'w' => 3,
        ],
        'domain_expiry' => [
            'title' => 'Domain Expired <30 Hari',
            'permission' => 'domains.view',
            'route' => 'subscriptions.index',
            'group' => 'Infrastruktur',
            'default_w' => 4, 'min_w' => 3, 'max_w' => 6,
            'w' => 3,
        ],
        'registrar_health' => [
            'title' => 'Registrar Health',
            'permission' => 'registrar_accounts.view',
            'route' => 'registrar-accounts.index',
            'group' => 'Infrastruktur',
            'default_w' => 4, 'min_w' => 3, 'max_w' => 6,
            'w' => 3,
        ],
        // P2 Sistem + Notifikasi
        'notifications_unread' => [
            'title' => 'Notifikasi Belum Dibaca',
            'permission' => 'notifications.view',
            'route' => 'notifications.index',
            'group' => 'Sistem',
            'default_w' => 3, 'min_w' => 3, 'max_w' => 4,
            'w' => 3,
        ],
        'notifications_action' => [
            'title' => 'Perlu Tindakan',
            'permission' => 'notifications.view',
            'route' => 'notifications.index',
            'group' => 'Sistem',
            'default_w' => 3, 'min_w' => 3, 'max_w' => 4,
            'w' => 3,
        ],
        // Map widget (C)
        'operational_map' => [
            'title' => 'Peta Operasional',
            'permission' => 'maps.view',
            'route' => 'operational-map.index',
            'group' => 'Infrastruktur',
            'default_w' => 6, 'min_w' => 6, 'max_w' => 12,
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

    public static function clampW(string $id, ?int $w): int
    {
        $def = self::WIDGETS[$id] ?? null;
        $default = $def['default_w'] ?? $def['w'] ?? 3;
        $min = $def['min_w'] ?? $default;
        $max = $def['max_w'] ?? $default;
        if ($w === null || ! in_array($w, self::PRESET_W, true)) {
            return $default;
        }
        if ($w < $min) return $min;
        if ($w > $max) return $max;
        // Jika masih bukan preset dalam rentang (misal min 3 max 4 tapi w=6), kembalikan default
        if (! in_array($w, array_filter(self::PRESET_W, fn ($v) => $v >= $min && $v <= $max), true)) {
            return $default;
        }
        return $w;
    }

    public static function colClass(int $w): string
    {
        // Responsive fallback: mobile selalu full, md/lg gunakan w tersimpan
        // Hindari semua widget jadi 1 baris penuh di desktop
        return match ($w) {
            3 => 'col-span-12 md:col-span-6 lg:col-span-3',
            4 => 'col-span-12 md:col-span-6 lg:col-span-4',
            6 => 'col-span-12 lg:col-span-6',
            8 => 'col-span-12 lg:col-span-8',
            12 => 'col-span-12',
            default => 'col-span-12 lg:col-span-6',
        };
    }

    private static function layout(array $ids): array
    {
        return array_map(fn ($id) => ['id' => $id, 'visible' => true, 'w' => self::WIDGETS[$id]['default_w'] ?? self::WIDGETS[$id]['w'] ?? 3], $ids);
    }

    public static function visibleForUser(\App\Models\User $user, ?array $preferences): array
    {
        $layout = $preferences['layout'] ?? self::defaultForRole($user);
        // Normalisasi w jika belum ada (migrasi lama) dan clamp
        $layout = array_map(function ($item) {
            $id = $item['id'] ?? null;
            if ($id && self::exists($id)) {
                $item['w'] = self::clampW($id, isset($item['w']) ? (int) $item['w'] : null);
            }
            return $item;
        }, $layout);
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