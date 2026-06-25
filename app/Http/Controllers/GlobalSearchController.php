<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Client;
use App\Models\Division;
use App\Models\HostingServer;
use App\Models\Invoice;
use App\Models\MetroEthernet;
use App\Models\Package;
use App\Models\Router;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $query = trim((string) $request->get('q', ''));

        if (strlen($query) < 2) {
            return response()->json(['results' => [], 'total' => 0, 'query' => $query]);
        }

        $results = $this->performSearch($request->user(), $query, 5);

        return response()->json([
            'results' => $results['groups'],
            'total' => $results['total'],
            'query' => $query,
        ]);
    }

    public function results(Request $request)
    {
        $query = trim((string) $request->get('q', ''));
        $moduleFilter = (string) $request->get('module', 'all');

        $results = strlen($query) >= 2
            ? $this->performSearch($request->user(), $query, 25, true, $moduleFilter)
            : ['groups' => [], 'total' => 0];

        return view('search.results', [
            'query' => $query,
            'groups' => $results['groups'],
            'total' => $results['total'],
            'selectedModule' => $moduleFilter,
        ]);
    }

    protected function performSearch($user, string $query, int $limitPerGroup, bool $includeCounts = false, string $moduleFilter = 'all'): array
    {
        $groups = [];
        $total = 0;

        foreach ($this->searchDefinitions($user) as $definition) {
            if ($moduleFilter !== 'all' && $moduleFilter !== $definition['key']) {
                continue;
            }

            /** @var Builder $builder */
            $builder = ($definition['query'])($query);
            $groupTotal = $includeCounts ? (clone $builder)->count() : null;
            $records = $builder->limit($limitPerGroup)->get($definition['columns']);

            if ($records->isEmpty()) {
                continue;
            }

            $items = $records->map($definition['map'])->values()->all();
            $matchedCount = $includeCounts ? (int) $groupTotal : count($items);

            $groups[] = [
                'key' => $definition['key'],
                'group' => $definition['group'],
                'icon' => $definition['icon'],
                'count' => $matchedCount,
                'items' => $items,
            ];

            $total += $matchedCount;
        }

        return [
            'groups' => $groups,
            'total' => $total,
        ];
    }

    protected function searchDefinitions($user): array
    {
        $definitions = [];

        if ($user?->can('branches.view')) {
            $definitions[] = [
                'key' => 'branches',
                'group' => 'Cabang',
                'icon' => 'building-2',
                'columns' => ['id', 'name', 'code', 'phone'],
                'query' => fn (string $query) => Branch::query()
                    ->where(function ($builder) use ($query) {
                        $builder->where('name', 'like', "%{$query}%")
                            ->orWhere('code', 'like', "%{$query}%")
                            ->orWhere('address', 'like', "%{$query}%")
                            ->orWhere('phone', 'like', "%{$query}%");
                    }),
                'map' => fn (Branch $branch) => [
                    'id' => $branch->id,
                    'title' => $branch->name,
                    'subtitle' => implode(' · ', array_filter([$branch->code, $branch->phone])),
                    'badge' => 'cabang',
                    'url' => route('branches.show', $branch),
                ],
            ];
        }

        if ($user?->can('divisions.view')) {
            $definitions[] = [
                'key' => 'divisions',
                'group' => 'Divisi',
                'icon' => 'briefcase-business',
                'columns' => ['id', 'name', 'description'],
                'query' => fn (string $query) => Division::query()
                    ->where(function ($builder) use ($query) {
                        $builder->where('name', 'like', "%{$query}%")
                            ->orWhere('description', 'like', "%{$query}%");
                    }),
                'map' => fn (Division $division) => [
                    'id' => $division->id,
                    'title' => $division->name,
                    'subtitle' => $division->description,
                    'badge' => 'divisi',
                    'url' => route('divisions.show', $division),
                ],
            ];
        }

        if ($user?->can('employees.view')) {
            $definitions[] = [
                'key' => 'employees',
                'group' => 'Karyawan',
                'icon' => 'user-cog',
                'columns' => ['id', 'name', 'email', 'phone', 'branch_id', 'division_id'],
                'query' => fn (string $query) => User::query()
                    ->whereHas('roles', function ($builder) {
                        $builder->whereIn('name', ['Owner', 'Admin', 'Employee', 'Billing', 'NOC', 'CS', 'Sales', 'Finance']);
                    })
                    ->where(function ($builder) use ($query) {
                        $builder->where('name', 'like', "%{$query}%")
                            ->orWhere('email', 'like', "%{$query}%")
                            ->orWhere('phone', 'like', "%{$query}%");
                    })
                    ->with(['branch:id,name', 'division:id,name', 'roles:id,name']),
                'map' => fn (User $employee) => [
                    'id' => $employee->id,
                    'title' => $employee->name,
                    'subtitle' => implode(' · ', array_filter([
                        $employee->email,
                        $employee->division?->name,
                        $employee->branch?->name,
                    ])),
                    'badge' => $employee->roles->first()?->name ?? 'staff',
                    'url' => route('employees.show', $employee),
                ],
            ];
        }

        if ($user?->can('clients.view')) {
            $definitions[] = [
                'key' => 'clients',
                'group' => 'Pelanggan',
                'icon' => 'users',
                'columns' => ['id', 'name', 'client_code', 'city', 'status'],
                'query' => fn (string $query) => Client::query()
                    ->where(function ($builder) use ($query) {
                        $builder->where('name', 'like', "%{$query}%")
                            ->orWhere('client_code', 'like', "%{$query}%")
                            ->orWhere('address', 'like', "%{$query}%")
                            ->orWhere('city', 'like', "%{$query}%");
                    }),
                'map' => fn (Client $client) => [
                    'id' => $client->id,
                    'title' => $client->name,
                    'subtitle' => implode(' · ', array_filter([$client->client_code, $client->city])),
                    'badge' => $client->status,
                    'url' => route('clients.show', $client),
                ],
            ];
        }

        if ($user?->can('subscriptions.view')) {
            $definitions[] = [
                'key' => 'subscriptions',
                'group' => 'Langganan',
                'icon' => 'wifi',
                'columns' => ['id', 'client_id', 'package_id', 'subscription_code', 'status'],
                'query' => fn (string $query) => Subscription::query()
                    ->where('subscription_code', 'like', "%{$query}%")
                    ->with(['client:id,name', 'package:id,name']),
                'map' => fn (Subscription $subscription) => [
                    'id' => $subscription->id,
                    'title' => $subscription->subscription_code,
                    'subtitle' => implode(' · ', array_filter([
                        $subscription->client?->name,
                        $subscription->package?->name,
                    ])),
                    'badge' => $subscription->status,
                    'url' => route('subscriptions.show', $subscription),
                ],
            ];
        }

        if ($user?->can('invoices.view')) {
            $definitions[] = [
                'key' => 'invoices',
                'group' => 'Invoice',
                'icon' => 'file-text',
                'columns' => ['id', 'client_id', 'invoice_number', 'status', 'total_amount'],
                'query' => fn (string $query) => Invoice::query()
                    ->where('invoice_number', 'like', "%{$query}%")
                    ->with('client:id,name'),
                'map' => fn (Invoice $invoice) => [
                    'id' => $invoice->id,
                    'title' => $invoice->invoice_number,
                    'subtitle' => implode(' · ', array_filter([
                        $invoice->client?->name,
                        'Rp ' . number_format((float) $invoice->total_amount, 0, ',', '.'),
                    ])),
                    'badge' => $invoice->status,
                    'url' => route('invoices.show', $invoice),
                ],
            ];
        }

        if ($user?->can('tickets.view')) {
            $definitions[] = [
                'key' => 'tickets',
                'group' => 'Tiket',
                'icon' => 'ticket',
                'columns' => ['id', 'client_id', 'ticket_number', 'subject', 'status'],
                'query' => fn (string $query) => Ticket::query()
                    ->where(function ($builder) use ($query) {
                        $builder->where('ticket_number', 'like', "%{$query}%")
                            ->orWhere('subject', 'like', "%{$query}%");
                    })
                    ->with('client:id,name'),
                'map' => fn (Ticket $ticket) => [
                    'id' => $ticket->id,
                    'title' => $ticket->ticket_number,
                    'subtitle' => implode(' · ', array_filter([$ticket->subject, $ticket->client?->name])),
                    'badge' => $ticket->status,
                    'url' => route('tickets.show', $ticket),
                ],
            ];
        }

        if ($user?->can('routers.view')) {
            $definitions[] = [
                'key' => 'routers',
                'group' => 'Router',
                'icon' => 'router',
                'columns' => ['id', 'branch_id', 'name', 'host', 'is_active'],
                'query' => fn (string $query) => Router::query()
                    ->where(function ($builder) use ($query) {
                        $builder->where('name', 'like', "%{$query}%")
                            ->orWhere('host', 'like', "%{$query}%")
                            ->orWhere('user', 'like', "%{$query}%")
                            ->orWhere('description', 'like', "%{$query}%");
                    })
                    ->with('branch:id,name'),
                'map' => fn (Router $router) => [
                    'id' => $router->id,
                    'title' => $router->name,
                    'subtitle' => implode(' · ', array_filter([$router->host, $router->branch?->name])),
                    'badge' => $router->is_active ? 'aktif' : 'nonaktif',
                    'url' => route('routers.show', $router),
                ],
            ];
        }

        if ($user?->can('servers.view')) {
            $definitions[] = [
                'key' => 'servers',
                'group' => 'Server Hosting',
                'icon' => 'server',
                'columns' => ['id', 'name', 'host', 'type', 'location', 'is_active'],
                'query' => fn (string $query) => HostingServer::query()
                    ->where(function ($builder) use ($query) {
                        $builder->where('name', 'like', "%{$query}%")
                            ->orWhere('host', 'like', "%{$query}%")
                            ->orWhere('type', 'like', "%{$query}%")
                            ->orWhere('location', 'like', "%{$query}%")
                            ->orWhere('description', 'like', "%{$query}%");
                    }),
                'map' => fn (HostingServer $server) => [
                    'id' => $server->id,
                    'title' => $server->name,
                    'subtitle' => implode(' · ', array_filter([$server->host, $server->location, $server->type])),
                    'badge' => $server->is_active ? 'aktif' : 'nonaktif',
                    'url' => route('servers.show', $server),
                ],
            ];
        }

        if ($user?->can('vendors.view')) {
            $definitions[] = [
                'key' => 'vendors',
                'group' => 'Vendor',
                'icon' => 'handshake',
                'columns' => ['id', 'name', 'cid', 'address'],
                'query' => fn (string $query) => Vendor::query()
                    ->where(function ($builder) use ($query) {
                        $builder->where('name', 'like', "%{$query}%")
                            ->orWhere('cid', 'like', "%{$query}%")
                            ->orWhere('address', 'like', "%{$query}%")
                            ->orWhere('notes', 'like', "%{$query}%");
                    }),
                'map' => fn (Vendor $vendor) => [
                    'id' => $vendor->id,
                    'title' => $vendor->name,
                    'subtitle' => implode(' · ', array_filter([$vendor->cid, $vendor->address])),
                    'badge' => 'vendor',
                    'url' => route('vendors.show', $vendor),
                ],
            ];
        }

        if ($user?->can('metro_ethernets.view')) {
            $definitions[] = [
                'key' => 'metro_ethernets',
                'group' => 'Metro Ethernet',
                'icon' => 'network',
                'columns' => ['id', 'vendor_id', 'name', 'cid', 'ip_address', 'bandwidth'],
                'query' => fn (string $query) => MetroEthernet::query()
                    ->where(function ($builder) use ($query) {
                        $builder->where('name', 'like', "%{$query}%")
                            ->orWhere('cid', 'like', "%{$query}%")
                            ->orWhere('ip_address', 'like', "%{$query}%")
                            ->orWhere('bandwidth', 'like', "%{$query}%")
                            ->orWhereHas('vendor', function ($vendorBuilder) use ($query) {
                                $vendorBuilder->where('name', 'like', "%{$query}%");
                            });
                    })
                    ->with('vendor:id,name'),
                'map' => fn (MetroEthernet $metro) => [
                    'id' => $metro->id,
                    'title' => $metro->display_name,
                    'subtitle' => implode(' · ', array_filter([
                        $metro->vendor?->name,
                        $metro->ip_address,
                        $metro->bandwidth ? $metro->bandwidth . ' Mbps' : null,
                    ])),
                    'badge' => $metro->cid ?: 'metro',
                    'url' => route('metro-ethernets.show', $metro),
                ],
            ];
        }

        if ($user?->can('services.view')) {
            $definitions[] = [
                'key' => 'services',
                'group' => 'Layanan',
                'icon' => 'box',
                'columns' => ['id', 'name', 'code', 'type', 'is_active'],
                'query' => fn (string $query) => Service::query()
                    ->where(function ($builder) use ($query) {
                        $builder->where('name', 'like', "%{$query}%")
                            ->orWhere('code', 'like', "%{$query}%")
                            ->orWhere('type', 'like', "%{$query}%")
                            ->orWhere('description', 'like', "%{$query}%");
                    }),
                'map' => fn (Service $service) => [
                    'id' => $service->id,
                    'title' => $service->name,
                    'subtitle' => implode(' · ', array_filter([$service->code, $service->type])),
                    'badge' => $service->is_active ? 'aktif' : 'nonaktif',
                    'url' => route('services.show', $service),
                ],
            ];
        }

        if ($user?->can('packages.view')) {
            $definitions[] = [
                'key' => 'packages',
                'group' => 'Paket',
                'icon' => 'package',
                'columns' => ['id', 'service_id', 'name', 'price', 'is_active'],
                'query' => fn (string $query) => Package::query()
                    ->where(function ($builder) use ($query) {
                        $builder->where('name', 'like', "%{$query}%")
                            ->orWhere('description', 'like', "%{$query}%")
                            ->orWhere('bandwidth_down', 'like', "%{$query}%")
                            ->orWhere('bandwidth_up', 'like', "%{$query}%")
                            ->orWhere('quota', 'like', "%{$query}%")
                            ->orWhereHas('service', function ($serviceBuilder) use ($query) {
                                $serviceBuilder->where('name', 'like', "%{$query}%")
                                    ->orWhere('code', 'like', "%{$query}%");
                            });
                    })
                    ->with('service:id,name'),
                'map' => fn (Package $package) => [
                    'id' => $package->id,
                    'title' => $package->name,
                    'subtitle' => implode(' · ', array_filter([
                        $package->service?->name,
                        'Rp ' . number_format((float) $package->price, 0, ',', '.'),
                    ])),
                    'badge' => $package->is_active ? 'aktif' : 'nonaktif',
                    'url' => route('packages.show', $package),
                ],
            ];
        }

        return $definitions;
    }
}
