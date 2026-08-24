# Dashboard

## Tujuan
Dashboard operasional menampilkan ringkasan real-time pelanggan, langganan, tagihan, tiket, infrastruktur, dan notifikasi yang perlu tindakan. Dapat dikustomisasi per pengguna (posisi/urutan dan ukuran widget via grid 12 preset 3/4/6/8/12) dengan default per role dan empty state "Belum ada data".

## Entitas Terkait
- `User.dashboard_preferences` (json `layout[]:{id,visible,w}`, `widget_periods`)
- `Client`, `Subscription`, `Package`, `Invoice`, `Payment`, `Ticket`, `Router`, `HostingServer`, `SubscriptionDomain`, `RegistrarAccount`, `AdminNotification`
- `Spatie\Activitylog\Models\Activity`

## Route Utama
| Method | URI | Controller | Permission |
|--------|-----|------------|------------|
| GET | `/dashboard` | `DashboardController@index` | `auth,verified,ip.restrict` (widget filter `@can`) |
| GET | `/dashboard/stats?widget&period` | `DashboardController@stats` | cek `WidgetRegistry` permission per widget; `router_server` allow `routers.view` OR `servers.view` |
| PUT | `/dashboard/preferences` | `DashboardController@updatePreferences` | `auth` — validasi `layout.*.id exists, visible bool, w in 3/4/6/8/12` + clamp `min_w/max_w` |

## Permission
Widget gate `@can` + server-side `stats`:
- `clients.view` — Total Pelanggan, Pertumbuhan
- `subscriptions.view` — Langganan per Status
- `invoices.view` / `payments.verify` / `financial_reports.view` — Outstanding, Revenue, Due, Perlu Verifikasi, Financial Attention (any of three)
- `tickets.view` / `tickets.assign` — Tiket Terbuka, Belum Respon
- `logs.view` — Aktivitas Terakhir
- `routers.view` / `servers.view` — Router/Server (any)
- `zabbix_monitors.view` / `registrar_accounts.view` / `maps.view` / `domains.view` — Operational Health (any infra), Kesehatan Zabbix, Registrar Health
- `notifications.view` — Notifikasi, Action Required
- `maps.view` — Peta Operasional
- `packages.view` — Paket Terlaris

`financial_attention` allow `invoices.view||payments.view||financial_reports.view`; `operational_health` allow any infra; `router_server` allow either.

## Alur Bisnis
1. `DashboardWidgetRegistry::defaultForRole()` — layout kanonik urutan + `w` default per widget (semua role satu urutan, filter visible per permission).
2. `DashboardStatsService` — query real + `Cache::remember 300s` per user+widget+period (key `dashboard:stats:{userId}:{widget}:{period}`); `recent_activity` 60s. Tidak bocor lintas user.
3. `DashboardController@index` — load `prefs` (fallback default), `visibleForUser()` (clamp `w`, filter permission), preload stats untuk visible widgets + `notifications` aggregate.
4. Blade `dashboard.blade.php` — grid 12 `colClass(w)` (3→`md:6 lg:3`, 4→`md:6 lg:4`, 6→`lg:6`, 8→`lg:8`, 12→`col-span-12`), periode per widget select `7d/30d/1y/1M` → `fetch /dashboard/stats`, kustomisasi modal SortableJS drag posisi + preset `w` select + `PUT` + `localStorage` fallback.
5. `updatePreferences` — validasi preset + `Registry::clampW()` sebelum `User::update`.

## Integrasi Modul Lain
- **Pusat Notifikasi:** `notifications_unread/action` (counts via `AdminNotificationService`), `financial_attention` (billing `actionRequired`), `operational_health` (infrastructure `actionRequired` + `registrar_issues` gated `registrar_accounts.view`), `view_map_filtered` (`maps.view` → `operational-map.index?branch_id/q`).
- **Operational Map:** widget `operational_map` preview (mapped/unmapped via `OperationalMapService::summary`).
- **Global Search, Activity Log:** tidak duplikasi query — stats reuse service.

## Seeder / Migration Terkait
- `2026_08_23_000002_add_dashboard_preferences_to_users_table.php` — `users.dashboard_preferences json nullable`
- `PermissionSeeder` — tidak ada permission baru `dashboard.*` (semua `auth` boleh lihat sesuai widgetnya)
- `SystemSetting` — tidak ada key baru; cache TTL default 300s di code

## Known Issues / Catatan
- `revenue` periode `30d` = sliding 30 hari, `1M` = bulan kalender, `1y` = 365 hari; test membuktikan 29 Jul (dalam 30d tapi luar Agustus) membedakan.
- `zabbix_health` masih placeholder; butuh `ZABBIX_API_URL` untuk sparkline nyata.
- Polling hanya `notifications/count` `setInterval 60s` di `layouts/header.blade.php`; stats tidak di-poll.
- NOC role perlu `maps.view` + `routers/servers` untuk melihat infra lengkap.
