<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Payment;
use App\Models\RegistrarAccount;
use App\Models\Router;
use App\Models\HostingServer;
use App\Models\Subscription;
use App\Models\SubscriptionDomain;
use App\Models\Ticket;
use App\Services\Admin\AdminNotificationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardStatsService
{
    /**
     * Semua stats di-cache 5 menit per user+period untuk hindari N+1 berat.
     * Key tidak bocor lintas permission karena permission gate di view, dan per user.
     */
    public function clientsCount(\App\Models\User $user, string $period = '30d'): array
    {
        return Cache::remember($this->key($user, 'clients_count', $period), 300, function () {
            $total = Client::count();
            $byStatus = Client::select('status', DB::raw('count(*) as c'))->groupBy('status')->pluck('c', 'status')->toArray();
            $recent = Client::where('created_at', '>=', now()->subDays(7))->count();
            return [
                'total' => $total,
                'by_status' => $byStatus,
                'recent_7d' => $recent,
                'empty' => $total === 0,
            ];
        });
    }

    public function subscriptionsStatus(\App\Models\User $user): array
    {
        return Cache::remember($this->key($user, 'subscriptions_status'), 300, function () {
            $byStatus = Subscription::select('status', DB::raw('count(*) as c'))->groupBy('status')->pluck('c', 'status')->toArray();
            $total = array_sum($byStatus);
            return ['by_status' => $byStatus, 'total' => $total, 'empty' => $total === 0];
        });
    }

    public function growth(\App\Models\User $user, string $period = '30d'): array
    {
        return Cache::remember($this->key($user, 'growth', $period), 300, function () use ($period) {
            $months = match ($period) {
                '7d' => 2,
                '1y' => 12,
                default => 6,
            };
            $labels = [];
            $data = [];
            for ($i = $months - 1; $i >= 0; $i--) {
                $d = now()->subMonths($i);
                $labels[] = $d->format('M');
                $data[] = Client::whereYear('registered_at', $d->year)->whereMonth('registered_at', $d->month)->count();
                // Fallback ke created_at jika registered_at null
                if (end($data) === 0) {
                    $data[count($data) - 1] = Client::whereYear('created_at', $d->year)->whereMonth('created_at', $d->month)->count();
                }
            }
            return ['labels' => $labels, 'data' => $data, 'empty' => array_sum($data) === 0];
        });
    }

    public function topPackages(\App\Models\User $user): array
    {
        return Cache::remember($this->key($user, 'top_packages'), 300, function () {
            $rows = Package::withCount('subscriptions')->orderByDesc('subscriptions_count')->limit(5)->get(['id', 'name', 'service_id']);
            $rows->load('service:id,name');
            return ['items' => $rows->toArray(), 'empty' => $rows->isEmpty()];
        });
    }

    public function outstandingInvoice(\App\Models\User $user): array
    {
        return Cache::remember($this->key($user, 'outstanding'), 300, function () {
            $statuses = ['unpaid', 'overdue', 'partially_paid'];
            $count = Invoice::whereIn('status', $statuses)->count();
            $total = Invoice::whereIn('status', $statuses)->sum('total_amount');
            // Aging
            $now = now();
            $aging = [
                '0-30' => Invoice::whereIn('status', $statuses)->where('due_date', '>=', $now->copy()->subDays(30))->sum('total_amount'),
                '31-60' => Invoice::whereIn('status', $statuses)->whereBetween('due_date', [$now->copy()->subDays(60), $now->copy()->subDays(31)])->sum('total_amount'),
                '61-90' => Invoice::whereIn('status', $statuses)->whereBetween('due_date', [$now->copy()->subDays(90), $now->copy()->subDays(61)])->sum('total_amount'),
                '>90' => Invoice::whereIn('status', $statuses)->where('due_date', '<', $now->copy()->subDays(90))->sum('total_amount'),
            ];
            return ['count' => $count, 'total' => (float) $total, 'aging' => $aging, 'empty' => $count === 0];
        });
    }

    public function revenue(\App\Models\User $user, string $period = '1M'): array
    {
        return Cache::remember($this->key($user, 'revenue', $period), 300, function () {
            $start = now()->startOfMonth();
            $end = now()->endOfMonth();
            $current = Payment::where('status', 'verified')->whereBetween('payment_date', [$start, $end])->sum('amount');
            $prevStart = now()->subMonth()->startOfMonth();
            $prevEnd = now()->subMonth()->endOfMonth();
            $prev = Payment::where('status', 'verified')->whereBetween('payment_date', [$prevStart, $prevEnd])->sum('amount');
            $pct = $prev > 0 ? round((($current - $prev) / $prev) * 100, 1) : null;
            return ['current' => (float) $current, 'prev' => (float) $prev, 'pct' => $pct, 'empty' => $current == 0 && $prev == 0];
        });
    }

    public function pendingPayments(\App\Models\User $user): array
    {
        return Cache::remember($this->key($user, 'pending_payments'), 300, function () {
            $c = Payment::where('status', 'pending')->count();
            return ['count' => $c, 'empty' => $c === 0];
        });
    }

    public function dueInvoices(\App\Models\User $user): array
    {
        return Cache::remember($this->key($user, 'due_invoices'), 300, function () {
            $list = Invoice::whereIn('status', ['unpaid', 'overdue', 'partially_paid'])
                ->whereBetween('due_date', [now(), now()->addDays(7)])
                ->orderBy('due_date')->limit(5)
                ->get(['id', 'invoice_number', 'due_date', 'total_amount', 'client_id'])
                ->load('client:id,name');
            return ['items' => $list->toArray(), 'empty' => $list->isEmpty()];
        });
    }

    public function ticketsOpen(\App\Models\User $user): array
    {
        return Cache::remember($this->key($user, 'tickets_open'), 300, function () {
            $byPriority = Ticket::whereNotIn('status', ['resolved', 'closed'])->select('priority', DB::raw('count(*) as c'))->groupBy('priority')->pluck('c', 'priority')->toArray();
            $total = array_sum($byPriority);
            return ['by_priority' => $byPriority, 'total' => $total, 'empty' => $total === 0];
        });
    }

    public function ticketsUnresponded(\App\Models\User $user): array
    {
        return Cache::remember($this->key($user, 'tickets_unresponded'), 300, function () {
            $c = Ticket::whereNull('first_response_at')->where('created_at', '<', now()->subDay())->whereNotIn('status', ['resolved', 'closed'])->count();
            return ['count' => $c, 'empty' => $c === 0];
        });
    }

    public function recentActivity(\App\Models\User $user): array
    {
        return Cache::remember($this->key($user, 'recent_activity'), 60, function () {
            $logs = \Spatie\Activitylog\Models\Activity::latest('id')->limit(5)->get(['id', 'log_name', 'description', 'causer_id', 'subject_type', 'subject_id', 'created_at']);
            $logs->load('causer:id,name');
            return ['items' => $logs->toArray(), 'empty' => $logs->isEmpty()];
        });
    }

    public function routerServer(\App\Models\User $user): array
    {
        return Cache::remember($this->key($user, 'router_server'), 300, function () {
            return [
                'routers_active' => Router::where('is_active', true)->count(),
                'routers_total' => Router::count(),
                'servers_active' => HostingServer::where('is_active', true)->count(),
                'servers_total' => HostingServer::count(),
                'empty' => Router::count() === 0 && HostingServer::count() === 0,
            ];
        });
    }

    public function domainExpiry(\App\Models\User $user): array
    {
        return Cache::remember($this->key($user, 'domain_expiry'), 300, function () {
            $list = SubscriptionDomain::whereBetween('expires_at', [now(), now()->addDays(30)])->orderBy('expires_at')->limit(5)->get(['id', 'domain_name', 'expires_at', 'subscription_id']);
            return ['items' => $list->toArray(), 'count' => $list->count(), 'empty' => $list->isEmpty()];
        });
    }

    public function registrarHealth(\App\Models\User $user): array
    {
        return Cache::remember($this->key($user, 'registrar_health'), 300, function () {
            $accounts = RegistrarAccount::select('id', 'name', 'is_active', 'last_error_summary', 'last_synced_at')->get();
            return ['items' => $accounts->toArray(), 'empty' => $accounts->isEmpty()];
        });
    }

    public function notifications(\App\Models\User $user): array
    {
        $service = app(AdminNotificationService::class);
        return [
            'unread' => $service->unreadCountForUser($user),
            'action_required' => $service->actionRequiredCountForUser($user),
        ];
    }

    private function key(\App\Models\User $user, string $widget, string $period = ''): string
    {
        $p = $period ? ":{$period}" : '';
        return "dashboard:stats:{$user->id}:{$widget}{$p}";
    }
}