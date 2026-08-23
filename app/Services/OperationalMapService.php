<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Client;
use Illuminate\Support\Facades\DB;

class OperationalMapService
{
    /**
     * Query pelanggan terpetakan dengan filter server-side.
     * Hanya field redacted yang dikembalikan.
     */
    public function locations(array $filters, \App\Models\User $user): array
    {
        $query = Client::query()
            ->with(['branch:id,name', 'subscriptions.package.service:id,name'])
            ->select(['id', 'name', 'client_code', 'status', 'city', 'province_code', 'regency_code', 'branch_id', 'latitude', 'longitude']);

        // Permission sudah dicek di controller (maps.view + clients.view)

        // Filter branch
        if (! empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        // Filter status pelanggan
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter subscription status (punya subscription dengan status tersebut)
        if (! empty($filters['subscription_status'])) {
            $query->whereHas('subscriptions', fn ($q) => $q->where('status', $filters['subscription_status']));
        }

        // Filter layanan (service)
        if (! empty($filters['service_id'])) {
            $query->whereHas('subscriptions.package', fn ($q) => $q->where('service_id', $filters['service_id']));
        }

        // Filter wilayah
        if (! empty($filters['province_code'])) {
            $query->where('province_code', $filters['province_code']);
        }
        if (! empty($filters['regency_code'])) {
            $query->where('regency_code', $filters['regency_code']);
        }

        // Filter mapped / unmapped
        $mappedFilter = $filters['mapped'] ?? null; // 'only' | 'unmapped' | null
        if ($mappedFilter === 'only') {
            $query->whereNotNull('latitude')->whereNotNull('longitude');
        } elseif ($mappedFilter === 'unmapped') {
            $query->where(function ($q) {
                $q->whereNull('latitude')->orWhereNull('longitude');
            });
        }

        // Search
        if (! empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function ($b) use ($q) {
                $b->where('name', 'like', "%{$q}%")
                    ->orWhere('client_code', 'like', "%{$q}%")
                    ->orWhere('city', 'like', "%{$q}%");
            });
        }

        // BBOX filter (viewport)
        if (! empty($filters['bbox'])) {
            // bbox: minLng,minLat,maxLng,maxLat
            $parts = explode(',', $filters['bbox']);
            if (count($parts) === 4) {
                [$minLng, $minLat, $maxLng, $maxLat] = array_map('floatval', $parts);
                $query->whereBetween('latitude', [$minLat, $maxLat])
                    ->whereBetween('longitude', [$minLng, $maxLng]);
            }
        }

        // Untuk endpoint locations, default hanya yang terpetakan kecuali diminta unmapped
        if ($mappedFilter !== 'unmapped' && empty($filters['include_unmapped'])) {
            // Jika tidak ada filter mapped explicit dan tidak include_unmapped, tampilkan hanya mapped untuk peta
            // Tapi summary tetap hitung unmapped terpisah
            if ($mappedFilter === null) {
                $query->whereNotNull('latitude')->whereNotNull('longitude');
            }
        }

        $limit = min(5000, max(100, (int) ($filters['limit'] ?? 2000)));
        $clients = $query->limit($limit)->get();

        // Build redacted data
        $data = $clients->map(function (Client $c) {
            $activeSubs = $c->subscriptions->where('status', 'active')->count();
            $serviceName = $c->subscriptions->first()?->package?->service?->name;
            return [
                'id' => $c->id,
                'name' => $c->name,
                'client_code' => $c->client_code,
                'status' => $c->status,
                'city' => $c->city,
                'branch_id' => $c->branch_id,
                'branch_name' => $c->branch?->name,
                'latitude' => $c->latitude !== null ? (float) $c->latitude : null,
                'longitude' => $c->longitude !== null ? (float) $c->longitude : null,
                'subscriptions_count' => $activeSubs,
                'service_name' => $serviceName,
                'type' => 'client',
            ];
        })->values()->toArray();

        // Branch markers
        $branches = [];
        if (empty($filters['branch_id']) || $filters['branch_id'] === 'all') {
            $branchQuery = Branch::whereNotNull('default_latitude')->whereNotNull('default_longitude');
            if (! empty($filters['province_code'])) {
                $branchQuery->where('default_province_code', $filters['province_code']);
            }
            $branches = $branchQuery->get(['id', 'name', 'code', 'default_latitude', 'default_longitude'])->map(fn ($b) => [
                'id' => $b->id,
                'name' => $b->name,
                'client_code' => $b->code,
                'status' => 'branch',
                'city' => null,
                'branch_id' => $b->id,
                'branch_name' => $b->name,
                'latitude' => (float) $b->default_latitude,
                'longitude' => (float) $b->default_longitude,
                'subscriptions_count' => 0,
                'service_name' => null,
                'type' => 'branch',
            ])->toArray();
        }

        // Merge for map (clients first, then branches)
        $all = array_merge($data, $branches);

        // Bounds
        $lats = array_filter(array_column($all, 'latitude'));
        $lngs = array_filter(array_column($all, 'longitude'));
        $bounds = null;
        if ($lats && $lngs) {
            $bounds = [
                'minLat' => min($lats),
                'maxLat' => max($lats),
                'minLng' => min($lngs),
                'maxLng' => max($lngs),
            ];
        }

        return [
            'data' => $all,
            'meta' => [
                'count' => count($data),
                'branch_count' => count($branches),
                'bounds' => $bounds,
                'limit' => $limit,
            ],
        ];
    }

    public function summary(array $filters, \App\Models\User $user): array
    {
        $base = Client::query();
        // Terapkan filter yang sama dengan locations agar konsisten (kecuali limit/include_unmapped dan default mapped)
        if (! empty($filters['branch_id']) && $filters['branch_id'] !== 'all') {
            $base->where('branch_id', $filters['branch_id']);
        }
        if (! empty($filters['status'])) {
            $base->where('status', $filters['status']);
        }
        if (! empty($filters['subscription_status'])) {
            $base->whereHas('subscriptions', fn ($q) => $q->where('status', $filters['subscription_status']));
        }
        if (! empty($filters['service_id'])) {
            $base->whereHas('subscriptions.package', fn ($q) => $q->where('service_id', $filters['service_id']));
        }
        if (! empty($filters['province_code'])) {
            $base->where('province_code', $filters['province_code']);
        }
        if (! empty($filters['regency_code'])) {
            $base->where('regency_code', $filters['regency_code']);
        }
        if (! empty($filters['q'])) {
            $q = $filters['q'];
            $base->where(function ($b) use ($q) {
                $b->where('name', 'like', "%{$q}%")
                    ->orWhere('client_code', 'like', "%{$q}%")
                    ->orWhere('city', 'like', "%{$q}%");
            });
        }
        if (! empty($filters['bbox'])) {
            $parts = explode(',', $filters['bbox']);
            if (count($parts) === 4) {
                [$minLng, $minLat, $maxLng, $maxLat] = array_map('floatval', $parts);
                $base->whereBetween('latitude', [$minLat, $maxLat])
                    ->whereBetween('longitude', [$minLng, $maxLng]);
            }
        }
        // mapped filter memotong base seperti di locations — agar summary konsisten dengan tampilan peta
        $mappedFilter = $filters['mapped'] ?? null;
        if ($mappedFilter === 'only') {
            $base->whereNotNull('latitude')->whereNotNull('longitude');
        } elseif ($mappedFilter === 'unmapped') {
            $base->where(function ($q) {
                $q->whereNull('latitude')->orWhereNull('longitude');
            });
        }

        $total = (clone $base)->count();
        $mapped = (clone $base)->whereNotNull('latitude')->whereNotNull('longitude')->count();
        $unmapped = $total - $mapped;

        $byBranch = (clone $base)
            ->select('branch_id', DB::raw('count(*) as count'))
            ->groupBy('branch_id')
            ->with('branch:id,name')
            ->get()
            ->map(fn ($r) => ['branch_id' => $r->branch_id, 'branch_name' => $r->branch?->name ?? 'Tanpa Cabang', 'count' => $r->count])
            ->toArray();

        $byStatus = (clone $base)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->map(fn ($c, $s) => ['status' => $s, 'count' => $c])
            ->values()
            ->toArray();

        $totalBranches = Branch::count();

        return [
            'total' => $total,
            'mapped' => $mapped,
            'unmapped' => $unmapped,
            'total_branches' => $totalBranches,
            'by_branch' => $byBranch,
            'by_status' => $byStatus,
        ];
    }
}