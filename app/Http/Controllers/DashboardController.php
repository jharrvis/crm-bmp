<?php

namespace App\Http\Controllers;

use App\Services\DashboardStatsService;
use App\Services\DashboardWidgetRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardStatsService $stats)
    {
        $user = $request->user();
        $prefs = $user->dashboard_preferences ?? DashboardWidgetRegistry::defaultForRole($user);
        // Normalisasi: jika prefs lama tanpa layout, fallback
        if (! isset($prefs['layout'])) {
            $prefs['layout'] = is_array($prefs) && isset($prefs[0]['id']) ? $prefs : DashboardWidgetRegistry::defaultForRole($user);
            $prefs['widget_periods'] = $prefs['widget_periods'] ?? [];
        }

        $periods = $prefs['widget_periods'] ?? [];
        $visible = DashboardWidgetRegistry::visibleForUser($user, $prefs);

        // Preload stats hanya untuk widget visible + gate permission (cache 5m di service)
        $data = [];
        foreach ($visible as $item) {
            $id = $item['id'];
            $period = $periods[$id] ?? $periods['growth'] ?? '30d';
            if ($id === 'growth') {
                $period = $periods[$id] ?? '30d';
            }
            if ($id === 'revenue') {
                $period = $periods[$id] ?? '1M';
            }
            $data[$id] = $this->statsForWidget($stats, $user, $id, $period);
        }

        // Notifikasi aggregate tetap via service (counts sudah cache 120s di service)
        $data['notifications'] = $stats->notifications($user);

        return view('dashboard', [
            'prefs' => $prefs,
            'visible' => $visible,
            'stats' => $data,
            'registry' => DashboardWidgetRegistry::all(),
        ]);
    }

    public function stats(Request $request, DashboardStatsService $stats)
    {
        $request->validate([
            'widget' => 'required|string',
            'period' => 'nullable|string|in:7d,30d,1y,1M,7H,30H',
        ]);
        $user = $request->user();
        $widget = $request->string('widget');
        $period = $request->string('period', '30d');

        if (! DashboardWidgetRegistry::exists($widget)) {
            abort(404);
        }
        $perm = DashboardWidgetRegistry::all()[$widget]['permission'] ?? null;
        if ($perm && ! $user->can($perm)) {
            abort(403);
        }
        // Khusus router_server allow either
        if ($widget === 'router_server' && ! ($user->can('routers.view') || $user->can('servers.view'))) {
            abort(403);
        }

        $data = $this->statsForWidget($stats, $user, $widget, $period);
        // Bust cache jika diminta? Tidak — service sudah cache 5m

        return response()->json(['widget' => $widget, 'period' => $period, 'data' => $data]);
    }

    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'layout' => 'required|array',
            'layout.*.id' => 'required|string',
            'layout.*.visible' => 'required|boolean',
            'widget_periods' => 'nullable|array',
            'widget_periods.*' => 'nullable|string|max:10',
        ]);

        // Validasi id exists di registry
        foreach ($validated['layout'] as $item) {
            if (! DashboardWidgetRegistry::exists($item['id'])) {
                return response()->json(['message' => "Widget {$item['id']} tidak dikenal"], 422);
            }
        }

        $user = $request->user();
        $prefs = [
            'layout' => $validated['layout'],
            'widget_periods' => $validated['widget_periods'] ?? $user->dashboard_preferences['widget_periods'] ?? [],
        ];
        $user->update(['dashboard_preferences' => $prefs]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'preferences' => $prefs]);
        }

        return back()->with('success', 'Preferensi dashboard disimpan.');
    }

    private function statsForWidget(DashboardStatsService $stats, $user, string $id, string $period): array
    {
        return match ($id) {
            'clients_count' => $stats->clientsCount($user, $period),
            'subscriptions_status' => $stats->subscriptionsStatus($user),
            'growth' => $stats->growth($user, $period),
            'top_packages' => $stats->topPackages($user),
            'outstanding_invoice' => $stats->outstandingInvoice($user),
            'revenue' => $stats->revenue($user, $period),
            'pending_payments' => $stats->pendingPayments($user),
            'due_invoices' => $stats->dueInvoices($user),
            'tickets_open' => $stats->ticketsOpen($user),
            'tickets_unresponded' => $stats->ticketsUnresponded($user),
            'recent_activity' => $stats->recentActivity($user),
            'router_server' => $stats->routerServer($user),
            'domain_expiry' => $stats->domainExpiry($user),
            'registrar_health' => $stats->registrarHealth($user),
            'zabbix_health' => ['empty' => true, 'note' => 'Gunakan halaman Zabbix'],
            'notifications_unread', 'notifications_action' => $stats->notifications($user),
            'operational_map' => ['empty' => true],
            default => ['empty' => true],
        };
    }
}