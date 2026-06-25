<?php

namespace App\View\Components;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app', [
            'breadcrumbs' => $this->buildBreadcrumbs(),
        ]);
    }

    protected function buildBreadcrumbs(): array
    {
        $route = request()->route();
        $routeName = $route?->getName();

        if (! $routeName || $routeName === 'dashboard') {
            return [];
        }

        $breadcrumbs = [
            [
                'label' => 'Dashboard',
                'url' => route('dashboard'),
                'current' => false,
            ],
        ];

        $config = $this->resolveRouteConfig($routeName);

        if (! $config) {
            $breadcrumbs[] = [
                'label' => $this->humanizeLabel(last(explode('.', $routeName))) ?: 'Halaman',
                'url' => null,
                'current' => true,
            ];

            return $breadcrumbs;
        }

        if (! empty($config['group'])) {
            $breadcrumbs[] = [
                'label' => $config['group'],
                'url' => null,
                'current' => false,
            ];
        }

        if (! empty($config['module_label'])) {
            $breadcrumbs[] = [
                'label' => $config['module_label'],
                'url' => $this->routeUrl($config['index_route'] ?? null),
                'current' => false,
            ];
        }

        foreach ($this->resolveTrailItems($routeName, $config) as $item) {
            $breadcrumbs[] = $item;
        }

        if (count($breadcrumbs) > 0) {
            $breadcrumbs[array_key_last($breadcrumbs)]['current'] = true;
            if ($breadcrumbs[array_key_last($breadcrumbs)]['current']) {
                $breadcrumbs[array_key_last($breadcrumbs)]['url'] = null;
            }
        }

        return $this->dedupeBreadcrumbs($breadcrumbs);
    }

    protected function resolveRouteConfig(string $routeName): ?array
    {
        $configs = [
            'subscriptions.topology' => ['group' => 'Pelanggan', 'module_label' => 'Langganan', 'index_route' => 'subscriptions.index', 'param' => 'subscription', 'detail_label' => 'Topologi'],
            'topology.templates' => ['group' => 'Pelanggan', 'module_label' => 'Langganan', 'index_route' => 'subscriptions.index', 'detail_label' => 'Template Topologi'],
            'profile' => ['module_label' => 'Profil', 'index_route' => 'profile.edit'],
            'search' => ['module_label' => 'Pencarian Global', 'index_route' => 'search'],
            'branches' => ['group' => 'Organisasi', 'module_label' => 'Cabang', 'index_route' => 'branches.index', 'param' => 'branch'],
            'divisions' => ['group' => 'Organisasi', 'module_label' => 'Divisi', 'index_route' => 'divisions.index', 'param' => 'division'],
            'employees' => ['group' => 'Organisasi', 'module_label' => 'Karyawan', 'index_route' => 'employees.index', 'param' => 'employee'],
            'routers' => ['group' => 'Infrastruktur', 'module_label' => 'Router', 'index_route' => 'routers.index', 'param' => 'router'],
            'servers' => ['group' => 'Infrastruktur', 'module_label' => 'Hosting Server', 'index_route' => 'servers.index', 'param' => 'server'],
            'vendors' => ['group' => 'Infrastruktur', 'module_label' => 'Vendor', 'index_route' => 'vendors.index', 'param' => 'vendor'],
            'metro-ethernets' => ['group' => 'Infrastruktur', 'module_label' => 'Metro Ethernet', 'index_route' => 'metro-ethernets.index', 'param' => 'metro_ethernet'],
            'zabbix-monitors' => ['group' => 'Infrastruktur', 'module_label' => 'Zabbix Monitors', 'index_route' => 'zabbix-monitors.index'],
            'services' => ['group' => 'Produk & Layanan', 'module_label' => 'Layanan', 'index_route' => 'services.index', 'param' => 'service'],
            'packages' => ['group' => 'Produk & Layanan', 'module_label' => 'Paket', 'index_route' => 'packages.index', 'param' => 'package'],
            'clients' => ['group' => 'Pelanggan', 'module_label' => 'Manajemen Pelanggan', 'index_route' => 'clients.index', 'param' => 'client'],
            'subscriptions' => ['group' => 'Pelanggan', 'module_label' => 'Langganan', 'index_route' => 'subscriptions.index', 'param' => 'subscription'],
            'invoices' => ['group' => 'Billing', 'module_label' => 'Invoice', 'index_route' => 'invoices.index', 'param' => 'invoice'],
            'tickets' => ['group' => 'Support', 'module_label' => 'Ticket Support', 'index_route' => 'tickets.index', 'param' => 'ticket'],
            'ticket-canned-responses' => ['group' => 'Support', 'module_label' => 'Ticket Support', 'index_route' => 'tickets.index', 'detail_label' => 'Template Balasan'],
            'roles' => ['group' => 'Sistem', 'module_label' => 'Manajemen Role', 'index_route' => 'roles.index', 'param' => 'role'],
            'system-updates' => ['group' => 'Sistem', 'module_label' => 'Pembaruan Sistem', 'index_route' => 'system-updates.index'],
            'documentation' => ['group' => 'Sistem', 'module_label' => 'Dokumentasi', 'index_route' => 'documentation.index'],
            'activity-logs' => ['group' => 'Sistem', 'module_label' => 'Activity Log', 'index_route' => 'activity-logs.index'],
            'settings' => ['group' => 'Sistem', 'module_label' => 'Pengaturan', 'index_route' => 'settings.index'],
        ];

        $matchedPrefix = null;
        $matchedConfig = null;

        foreach ($configs as $prefix => $config) {
            if ($routeName === $prefix || Str::startsWith($routeName, $prefix . '.')) {
                if ($matchedPrefix === null || strlen($prefix) > strlen($matchedPrefix)) {
                    $matchedPrefix = $prefix;
                    $matchedConfig = $config + ['prefix' => $prefix];
                }
            }
        }

        return $matchedConfig;
    }

    protected function resolveTrailItems(string $routeName, array $config): array
    {
        $items = [];
        $action = Str::afterLast($routeName, '.');
        $resource = ! empty($config['param']) ? request()->route($config['param']) : null;
        $resourceLabel = $this->resolveResourceLabel($resource, $config['param'] ?? null);

        if ($routeName === ($config['prefix'] ?? null)) {
            return $items;
        }

        if (! empty($config['detail_label']) && Str::startsWith($routeName, $config['prefix'] . '.')) {
            if ($resource && $resourceLabel) {
                $items[] = [
                    'label' => $resourceLabel,
                    'url' => $this->resolveResourceShowUrl($config, $resource),
                    'current' => false,
                ];
            }

            $items[] = [
                'label' => $config['detail_label'],
                'url' => null,
                'current' => true,
            ];

            return $items;
        }

        if ($action === 'index') {
            return $items;
        }

        if ($action === 'create') {
            $items[] = ['label' => 'Tambah', 'url' => null, 'current' => true];
            return $items;
        }

        if ($action === 'show') {
            $items[] = ['label' => $resourceLabel ?: 'Detail', 'url' => null, 'current' => true];
            return $items;
        }

        if ($action === 'edit') {
            if ($resource && $resourceLabel) {
                $items[] = [
                    'label' => $resourceLabel,
                    'url' => $this->resolveResourceShowUrl($config, $resource),
                    'current' => false,
                ];
            }

            $items[] = ['label' => 'Edit', 'url' => null, 'current' => true];
            return $items;
        }

        if ($resource && $resourceLabel) {
            $items[] = [
                'label' => $resourceLabel,
                'url' => $this->resolveResourceShowUrl($config, $resource),
                'current' => false,
            ];
        }

        $items[] = [
            'label' => $this->humanizeLabel($action),
            'url' => null,
            'current' => true,
        ];

        return $items;
    }

    protected function resolveResourceShowUrl(array $config, mixed $resource): ?string
    {
        $prefix = $config['prefix'] ?? null;

        if (! $prefix || ! $resource || ! \Route::has($prefix . '.show')) {
            return null;
        }

        try {
            return route($prefix . '.show', $resource);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function resolveResourceLabel(mixed $resource, ?string $param = null): ?string
    {
        if ($resource instanceof Model) {
            foreach (['name', 'title', 'subject', 'invoice_number', 'ticket_number', 'subscription_code', 'client_code', 'code', 'circuit_id'] as $field) {
                $value = data_get($resource, $field);
                if (filled($value)) {
                    return (string) $value;
                }
            }

            return class_basename($resource) . ' #' . $resource->getKey();
        }

        if (filled($resource)) {
            return (string) $resource;
        }

        return match ($param) {
            'subscription' => 'Langganan',
            'invoice' => 'Invoice',
            'ticket' => 'Tiket',
            default => null,
        };
    }

    protected function humanizeLabel(string $value): string
    {
        return Str::title(str_replace(['-', '_', '.'], ' ', $value));
    }

    protected function routeUrl(?string $routeName): ?string
    {
        if (! $routeName || ! \Route::has($routeName)) {
            return null;
        }

        return route($routeName);
    }

    protected function dedupeBreadcrumbs(array $breadcrumbs): array
    {
        $deduped = [];

        foreach ($breadcrumbs as $item) {
            $last = end($deduped);

            if ($last && $last['label'] === $item['label']) {
                continue;
            }

            $deduped[] = $item;
        }

        return $deduped;
    }
}
