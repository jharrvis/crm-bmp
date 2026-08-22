# Plan: Dashboard Operasional — Data Real, Widget Customizable, dan Notification-aware

## 1. Tujuan

Mengubah dashboard utama dari hardcode demo menjadi dashboard operasional real yang informatif, interaktif, dan customizable per pengguna — tanpa mengganggu IP restrict dan tanpa membatasi API `client-portal/*`.

Dashboard bukan sumber event notifikasi. Event operasional dibuat oleh modul, job, atau event listener melalui pusat notifikasi global, lalu dashboard menampilkan agregasi dan attention queue sesuai permission pengguna.

Sumber kebenaran: `AGENTS.md` §4-§6, §10-§11, §22 (graphify), §16 checklist.

## 2. Kondisi Saat Ini (Riset)

- Route `routes/web.php:12` masih closure `fn()=>view('dashboard')` tanpa controller/data. `resources/views/dashboard.blade.php:1-218` hardcoded: `4,284 Pelanggan`, `99.9% SLA 23d 12h`, `12 Tiket`, `842.5M Revenue`, chart demo `120,190,300,500,200,300` / `12,5,3`, tabel `for $i<3` fake. Tidak ada `DashboardController`/`DashboardService`.
- `User` belum punya preferensi (`dashboard_preferences` tidak ada). `SystemSetting` hanya global (billing/notifications/domain_registrar). Butuh storage per-user.
- Fondasi pusat notifikasi sudah tersedia: `AdminNotification`, `AdminNotificationService`, `AdminNotificationController`, migration `admin_notifications`, header bell, halaman inbox, permission `notifications.*`, serta job domain/SSL/registrar. Dashboard harus mengonsumsi fondasi ini, bukan membuat pipeline notifikasi kedua.
- UI stack: `x-app-layout`, Tailwind `rounded-[2rem]`, `lucide`, `chart.js`, Alpine di `header.blade.php`, `public/assets/js/script.js: initCharts()` dark-aware.
- Permission: semua gate via `@can('module.view')` di `layouts/sidebar.blade.php`; dashboard harus ikut pola yang sama, bukan `@role()` (AGENTS.md §19.2).
- Layout sudah IP-restricted `['auth','verified','ip.restrict']` — dashboard otomatis ikut; API tidak.

## 3. Keputusan User (Final Scope)

1. Customizable: **show/hide + urutan saja** (tanpa resize `w`).
2. **Default layout per role** (Owner/Admin full, Billing/Finance fokus keuangan, NOC fokus infra/Zabbix, CS fokus klien+tiket, Sales fokus klien+paket).
3. **Periode per widget** (bukan global). Header `24 Jam/7 Hari/30 Hari` di `dashboard.blade.php:22-33` dihapus — tiap widget punya periode sendiri.
4. DB kosong → **"Belum ada data"** empty state (bukan angka demo).

## 4. Katalog Widget (Rekomendasi — 17 Widget)

> Filter tampil per permission; hide group jika tidak punya akses (mirip sidebar `can('routers.view') || can('servers.view') || can('zabbix_monitors.view')`).

### A. Bisnis — P0

| Widget | Query | Permission | Interaksi |
|--------|-------|------------|-----------|
| **Total Pelanggan** | `Client::count()` + `active/inactive/suspended/prospect` | `clients.view` | Sparkline 7h, klik → `clients.index?status=active` |
| **Langganan per Status** | `Subscription::groupBy status` (active/suspended/terminated) donut | `subscriptions.view` | Donut + legend |
| **Pertumbuhan Pelanggan** | `Client::whereYear registered_at groupBy month` line chart | `clients.view` | Periode widget: 7H/30H/1T, download PNG |
| **Paket Terlaris Top 5** | `Package::withCount('subscriptions') orderBy desc` bar horizontal | `packages.view` | Klik → `packages.show` |

### B. Keuangan — P0 (Billing/Finance/Owner)

| Widget | Query | Permission | Interaksi |
|--------|-------|------------|-----------|
| **Outstanding Invoice** | `Invoice::whereIn status [unpaid,overdue,partially_paid] sum total_amount + count` | `invoices.view` | Aging 0-30/31-60/61-90/>90 reuse `FinancialReportController` |
| **Revenue Bulan Ini** | `Payment::where status verified whereMonth payment_date sum amount` vs bulan lalu % | `financial_reports.view` | Periode: bulan ini/lalu |
| **Pembayaran Perlu Verifikasi** | `Payment::where status pending count` | `payments.verify` | Tombol Verifikasi → `payments.index?status=pending` |
| **Invoice Jatuh Tempo 7 Hari** | List 5 `orderBy due_date` | `invoices.view` | Klik → `invoices.show` |

### C. Support — P0

| Widget | Query | Permission | Interaksi |
|--------|-------|------------|-----------|
| **Tiket Terbuka per Prioritas** | `Ticket::whereNotIn [resolved,closed] groupBy priority/status` donut | `tickets.view` | High/Urgent merah |
| **Tiket Belum Respon >24j** | `whereNull first_response_at where created_at < now-24h` | `tickets.view` | Alert + link |
| **Aktivitas Terakhir (real)** | `activity_log` 5 terbaru (ganti fake) | `logs.view` atau semua auth | Link `activity-logs.index` |

### D. Infrastruktur — P1 (NOC/Owner)

| Widget | Query | Permission | Interaksi |
|--------|-------|------------|-----------|
| **Kesehatan Zabbix Ringkas** | `ZabbixService::getBandwidthData` 24h sparkline IN/OUT cache 2m | `zabbix_monitors.view` | Jika `ZABBIX_API_URL` kosong → warning; pause/live |
| **Router/Server Status** | `Router::where is_active count`, `HostingServer::count` + snapshot terakhir | `routers.view`/`servers.view` | Badge aktif/nonaktif |
| **Domain Expired <30 Hari** | `SubscriptionDomain::where expires_at between now..+30d` | `domains.view` | List + `CheckDomainExpiry` 07:00 |
| **Registrar Health** | `RegistrarAccount` sync_status/ last_error | `registrar_accounts.view` | `Test Koneksi` CTA |

### E. Sistem — P2

| Widget | Query | Permission | Interaksi |
|--------|-------|------------|-----------|
| **Notifikasi Belum Dibaca** | `AdminNotification::unread()->forUser(Auth::user())->count()` | `notifications.view` | Selalu tampil |
| **System Updates Pending** | `SystemUpdate` count | `system_updates.view` | Opsional |

### F. Notification-aware — P0/P1

| Widget | Sumber | Permission | Interaksi |
|--------|--------|------------|-----------|
| **Notifikasi Belum Dibaca** | `AdminNotification` unread aktif | `notifications.view` | Buka pusat notifikasi |
| **Action Required** | `AdminNotification` dengan `action_required=true`, belum resolved/dismissed | `notifications.view` | Filter berdasarkan severity dan kategori |
| **Operational Health** | Notification registry + health summaries | Permission sumber | Buka item registrar/server/Zabbix |
| **Financial Attention** | Notification registry kategori billing | `invoices.view`/`payments.view`/`financial_reports.view` | Buka invoice atau pembayaran |

Widget domain expiry, SSL expiry, invoice overdue, ticket priority, dan registrar health harus memakai query/service yang sama dengan producer notifikasinya agar angka widget dan inbox tidak berbeda.

Widget lanjutan `operational_map` direncanakan dalam `docs/plans/operational-map-network-coverage.md`. Dashboard hanya menampilkan ringkasan mapped/unmapped dan cabang dominan; halaman penuh menangani marker, filter, clustering, layer, dan popup.

Semua card reuse style `bg-white dark:bg-slate-800 p-6 md:p-8 rounded-[2rem] border border-slate-100 dark:border-slate-700 shadow-sm` + badge map di `header.blade.php: getBadgeClass()`.

## 5. Customizable — Desain

### 5.1 Storage (Rekomendasi Opsi A)

- Migrasi `2026_08_23_000001_add_dashboard_preferences_to_users_table.php`: `json('dashboard_preferences')->nullable()` + cast di `User` `$casts['dashboard_preferences'=>'array']`.
- Alasan: 1 migrasi, tidak perlu model baru, mirip `SystemSetting` json tapi per-user. Alternatif B (table terpisah) over-engineering untuk MVP.

### 5.2 Struktur JSON

```json
{
  "layout": [
    {"id":"clients_count","visible":true},
    {"id":"revenue","visible":true},
    {"id":"zabbix_health","visible":false}
  ],
  "widget_periods": {
    "growth":"30d",
    "revenue":"1M",
    "tickets":"7d"
  }
}
```

- `layout` = urutan + visible. `w` tidak disimpan (fixed `grid-cols-12` → `col-span-3/6/12` per widget default). Tidak ada resize.
- `widget_periods` = periode per widget (opsional).
- Default jika `null` → generate dari `DashboardWidgetRegistry::defaultForRole(Auth::user())` (per role).

### 5.3 Registry (Single Source of Truth)

`app/Services/DashboardWidgetRegistry.php` — array `id => [title, permission, route, group, default_w, defaultVisiblePerRole]`. Dipakai controller, Blade `@can`, dan JS. Mirror `RoleController::$moduleGroups` agar sidebar dan dashboard sinkron.

Contoh:
```php
'clients_count' => ['title'=>'Total Pelanggan','permission'=>'clients.view','route'=>'clients.index','group'=>'Bisnis','w'=>3],
'zabbix_health' => ['title'=>'Kesehatan Zabbix','permission'=>'zabbix_monitors.view','route'=>'zabbix-monitors.index','group'=>'Infrastruktur','w'=>6],
```

### 5.4 Kontrak dengan Notification Registry

Pusat notifikasi memiliki registry terpisah `NotificationTypeRegistry` yang mendefinisikan `category`, `severity`, `action_required`, permission, action label, dan apakah tipe tersebut tampil di dashboard. `DashboardWidgetRegistry` hanya membaca agregasi dari registry tersebut.

URL atau action tidak boleh dipercaya dari payload user. Resolver server-side harus memeriksa permission dan sumber entity sebelum membuat CTA.

### 5.5 Alur Persist

- Load: `DashboardController@index` → `$prefs = Auth::user()->dashboard_preferences ?? Registry::defaultForRole($user)` → pass ke Blade `x-data="dashboardCustom(@js($prefs), @js($registry))"`.
- Save show/hide & urutan: Alpine `SortableJS` drag → `PUT /dashboard/preferences` (`auth,verified,ip.restrict`) валидасы `layout.*.id exists:registry`, `visible boolean`, `period in [7d,30d,1y]` → `Auth::user()->update(['dashboard_preferences'=>$validated])`. Fallback `localStorage dashboard:layout:{userId}` jika fetch gagal.

## 6. Arsitektur

### Backend

- `app/Services/DashboardStatsService.php` — metode `clientsStats(period)`, `subscriptionStats()`, `invoiceStats(period)`, `ticketStats()`, `revenueStats()`, `zabbixQuick(period)`, `recentActivity()`, `domainExpiry()`, `notificationAttention()`. Semua `Cache::remember("dashboard:stats:{userId}:{period}", 300, fn)` + `withCount`/`sum` (reuse pola `FinancialReportController`). Extract jika >50 baris (AGENTS.md §11.2).
- `app/Http/Controllers/DashboardController.php` — `__construct` tanpa `permission:dashboard.view` (semua auth boleh lihat sesuai widgetnya). `index()` load prefs+stats, `updatePreferences(Request)` валидасы + update user. Log `activity('dashboard')->log('update_preferences')` jika relevan.
- `routes/web.php` — ganti closure:
  ```php
  Route::get('/dashboard',[DashboardController::class,'index'])->name('dashboard');
  Route::put('/dashboard/preferences',[DashboardController::class,'updatePreferences'])->name('dashboard.preferences');
  ```
  Tetap dalam grup `['auth','verified','ip.restrict']`.
- `app/View/Components/AppLayout.php` — tambah `dashboard` ke `resolveRouteConfig()` untuk breadcrumb.
- `config/app.php` tidak perlu env baru kecuali `DASHBOARD_CACHE_TTL=300` (default aman).

### Frontend

- `resources/views/dashboard.blade.php` — refactor jadi grid Alpine:
  - Ganti header `24 Jam/7 Hari/30 Hari` (hapus).
  - Grid `grid grid-cols-12 gap-6` → tiap widget `col-span-12 md:col-span-6 lg:col-span-3` (stat) atau `lg:col-span-6` (chart). Bungkus `@can('clients.view') ... @endcan`.
  - Komponen `resources/views/components/dashboard/stat-card.blade.php` + `chart-card.blade.php` reuse `rounded-[2rem] border shadow-sm` + `group-hover:scale-110`.
  - Empty state: `@forelse` else `Belum ada data` (sesuai keputusan 4).
  - Periode per widget: `select` kecil di card header (7H/30H/1T) → `fetch /dashboard/stats?widget=growth&period=30d` via `chart.js` update.
- Lib: `SortableJS` CDN (15KB, tanpa build) untuk drag urutan; tidak pakai GridStack (resize tidak dibutuhkan). Dark mode aware: `gridColor #334155` sudah ada di `script.js: initCharts()`.
- `public/assets/js/script.js` — tambah `dashboardCustom()` Alpine, tidak duplikasi `initCharts` (reuse).

### Integrasi Pusat Notifikasi

- Dashboard menampilkan `unread`, `action_required`, `severity`, `category`, dan `resolved` secara agregat.
- Klik widget membuka halaman pusat notifikasi dengan filter `category`, `severity`, atau `action_required`.
- Tombol action tetap menjalankan route modul sumber; setelah berhasil, modul sumber menandai notifikasi sebagai resolved, bukan sekadar read.
- Notifikasi informatif tetap dapat tampil di inbox, tetapi widget dashboard memprioritaskan notifikasi actionable/high/critical.

### Keamanan & Performance

- `@can` di Blade + `Gate` di controller stats (jangan expose data tanpa permission).
- Cache 5m per user+period, lock `Cache::lock('dashboard:stats', 10)` cegah stampede.
- Zabbix try/catch → tampil warning “Konfigurasi Zabbix belum lengkap” (service throw `RuntimeException`).

## 7. Tahapan Implementasi

### Fase 1 — Fondasi Dashboard (Estimasi 1 hari)

- Migrasi `add_dashboard_preferences_to_users_table`, cast di `User`.
- `DashboardWidgetRegistry` + `DashboardStatsService` (real queries, cache 5m) + `FinancialReportController` reuse untuk aging/revenue.
- Definisikan adapter query bersama untuk widget yang sumbernya juga menghasilkan notifikasi.
- `DashboardController@index` + `updatePreferences`, routes, `AppLayout` breadcrumb, hapus header periode global.
- Test: `php artisan migrate`, `php artisan test`, `graphify update`.

**Kriteria selesai:** Dashboard render dengan data real (bukan hardcode), tanpa error permission.

### Fase 2 — Widget P0 (1–2 hari)

- 4 stat cards (Total Pelanggan, Langganan per Status, Outstanding Invoice, Tiket Terbuka) — ganti `dashboard.blade.php:4 cards`.
- Line chart pertumbuhan (ganti `growthChart` demo) + donut tiket (ganti `ticketChart`) dengan periode per widget + `Belum ada data`.
- Tabel aktivitas real dari `activity_log` (ganti fake `for $i<3`).
- Test dedikasi: `tests/Feature/DashboardTest.php` (permission gate, stats count, empty state).

### Fase 3 — Customizable (1 hari)

- Alpine + SortableJS drag urutan, modal checklist show/hide, `PUT /dashboard/preferences` + `localStorage` fallback.
- Default layout per role (Owner full, Billing/Finance keuangan, NOC infra, CS klien+tiket, Sales klien+paket).
- Docs `docs/modules/dashboard.md` + update `CHANGELOG.md` + `graphify update`.

### Fase 4 — Widget P1/P2 + Integrasi Notifikasi (opsional, 1–2 hari)

- Zabbix ringkas, Domain expiry <30h, Revenue, Paket terlaris, Action Required, Financial Attention, Router/Server.
- Download PNG chart, skeleton `hidden` tetap, dark mode polish.
- UAT dengan data real cabang.

## 8. Testing & Rollout

- Unit: `DashboardStatsService` count/sum, cache hit.
- Feature: `@can` gate (tanpa `clients.view` → widget tidak render), `updatePreferences` persist, default per role, empty state, periode per widget.
- Integrasi: widget notifikasi hanya menampilkan record sesuai audience/permission; item resolved tidak masuk Action Required; CTA yang berhasil mengubah sumber menghasilkan state resolved.
- Manual: login sebagai Owner → lihat semua; NOC → Zabbix muncul; Billing → revenue muncul; tanpa data → semua `Belum ada data`.
- Rollout: `php artisan migrate`, `php artisan config:clear`, `php artisan view:clear`. Tidak perlu seeder (registry default di code).

## 9. Dampak Deployment

- Migration `add_dashboard_preferences_to_users_table` (re-run aman, nullable).
- Tidak butuh env baru; `ALLOWED_IPS_CIDR` tetap.
- Queue worker tidak wajib (stats sync, bukan job).
- Queue worker tetap wajib untuk producer notifikasi yang dijalankan melalui job scheduler; dashboard hanya membaca hasilnya.
- `CHANGELOG.md` kategori `Added` + `Changed` + `Deployment Notes`.

## 10. Keputusan Terbuka (Jika Perlu)

- Apakah header “Halo, {{name}}! Status Stabil” tetap atau diganti ringkasan notifikasi?
- Butuh export PDF dashboard ringkas (reuse `InvoicePdfService`)? Tidak untuk MVP.
- Apakah widget `Action Required` menjadi widget default untuk semua role yang memiliki `notifications.view`? Rekomendasi: ya, dengan isi tersaring permission.
- Apakah dashboard memakai polling ringan atau refresh manual untuk unread count? Rekomendasi: polling count saja, bukan reload semua statistik.

## 11. Referensi

- `resources/views/dashboard.blade.php`, `routes/web.php`, `app/Models/*`, `app/Http/Controllers/FinancialReportController.php`, `app/Services/ZabbixService.php`, `database/seeders/PermissionSeeder.php`, `resources/views/layouts/sidebar.blade.php`, `docs/plans/*`.
