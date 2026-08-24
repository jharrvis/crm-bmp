# Operational Map

## Tujuan
Peta operasional menampilkan sebaran pelanggan (lat/lng) dan cabang (default_lat/lng) dengan filter, clustering, popup redacted, dan ringkasan mapped/unmapped. Widget dashboard hanya preview; halaman penuh menangani marker dan layer.

## Entitas Terkait
- `Client` (`latitude 10,8`, `longitude 11,8`, `branch_id`, `status`, `province_code`, `regency_code`, `city`) + `subscriptions.package.service`
- `Branch` (`default_latitude`, `default_longitude`)
- `Service` (filter layanan)
- `AdminNotification` (untuk map CTA `view_map_filtered`)

## Route Utama
| Method | URI | Controller | Permission |
|--------|-----|------------|------------|
| GET | `/operational-map` | `OperationalMapController@index` | `maps.view` + `clients.view` |
| GET | `/operational-map/locations` | `OperationalMapController@locations` | `maps.view` + `clients.view` — filter `branch_id/status/subscription_status/service_id/province_code/regency_code/mapped/q/bbox/limit` |
| GET | `/operational-map/summary` | `OperationalMapController@summary` | `maps.view` + `clients.view` — filter sama (konsisten) |

Middleware `['auth','verified','ip.restrict']` (global web). Pagination limit `100–5000` (default 2000).

## Permission
- `maps.view` — wajib untuk semua 3 route (Owner/Admin via all, NOC/Sales/CS/Employee additive di `PermissionSeeder`).
- `clients.view` — wajib tambahan (dual gate) agar user dengan `maps.view` saja tidak melihat data pelanggan.
- `maps.view` tanpa `clients.view` → 403 (test `test_clients_view_required_even_with_maps`).
- `zabbix_monitors.view` / `registrar_accounts.view` tidak diperlukan untuk MVP; layer infra ditunda.

## Alur Bisnis
1. `index` pass `branches` + `services` untuk filter dropdown.
2. `OperationalMapService::locations(filters, user)` — `Client` query `with branch/subscriptions`, apply filters branch/status/subscription_status/service/province/regency/mapped/q/bbox, default hanya mapped (kecuali `mapped=unmapped` atau `include_unmapped`), limit, map to redacted `id/name/client_code/status/city/branch_id/branch_name/lat/lng/subscriptions_count/service_name/type=client|branch`, branch markers `default_lat/lng`, bounds `minLat/maxLat/minLng/maxLng`, meta `count/branch_count/bounds/limit`.
3. `summary(filters)` — base query sama (tanpa limit/default mapped) → `total/mapped/unmapped/total_branches/by_branch/by_status` konsisten dengan locations.
4. View `operational-map/index.blade.php` — Leaflet 1.9.4 + MarkerCluster 1.4.1 (CDN), tile `config/maps.php` + attribution OSM selalu tampil, `Alpine operationalMap()` fetch `locations`+`summary` on init/filter change, `L.markerClusterGroup`, popup `name/client_code/status/branch/city/subscriptions/service` + `Lihat Detail Pelanggan` link, `fitBounds` + `locateMe` (geolocation), legend, empty state “Belum ada pelanggan terpetakan”.

## Integrasi Modul Lain
- **Dashboard:** widget `operational_map` (`w=6`) preview `mapped/unmapped/total_branches` via `DashboardStatsService::operationalMap()` (= `OperationalMapService::summary([], user)`).
- **Notifikasi:** `NotificationTypeRegistry::view_map_filtered` (`maps.view` → `operational-map.index?branch_id/q`) — payload `branch_id/city/domain_name` terstruktur.
- **Pencarian Lokasi:** tetap via `MapLocationController@search` (Nominatim backend), bukan per marker.
- **Client:** koordinat disimpan via form client Leaflet + Nominatim, tetap tercatat `activity_log`.

## Seeder / Migration Terkait
- Tidak ada migration baru untuk MVP (pakai existing `clients.latitude/longitude` + `branches.default_latitude/longitude`).
- `PermissionSeeder` tambah `maps.view` (additive, `forgetCachedPermissions`).
- `config/maps.php` — tile OSM, Nominatim URL/user-agent, tidak butuh API key.

## Known Issues / Catatan
- Clustering client-side (MarkerCluster); untuk >5k marker, pertimbangkan server-side grid aggregation pada zoom rendah.
- BBOX filter untuk viewport; pagination berbasis viewport belum full (limit 2000).
- Heatmap/coverage polygon ditunda sampai koordinat router/POP/tower/ODP tervalidasi (Fase Network Coverage).
- Branch tanpa `default_lat/lng` tidak muncul sebagai marker.
