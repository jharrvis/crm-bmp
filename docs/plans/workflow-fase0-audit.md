# Fase 0 Audit — Workflow Dashboard, Notification, Operational Map

> Output Fase 0 sesuai `implementation-workflow-dashboard-notification-map.md` §4.
> Commit graph: `616c261`, graph: 2762 nodes / 5189 edges / 348 communities — fresh.
> Tanggal audit: 2026-08-23

## 1. Dependency Map

```
Fase 0: Kontrak & Audit (SAAT INI)
  → Track A (Notification Core)  ┐
  → Track B (Dashboard Found.)    ├─ PARALEL setelah kontrak registry final
  → Track C (Operational Map MVP) ┘
  → Fase Integrasi (Dashboard–Notification–Map)
  → Fase Network Coverage (gated data geografis)
```

- Mengenai `A/B/C` bisa paralel karena tidak berbagi migration yang sama setelah Fase 0 (lihat §3).
- Integrasi harus menunggu kriteria minimum Tracks terpenuhi; coverage menunggu survey/geo valid.

## 2. Permission Matrix (Aktual vs Rencana)

**Existing (PermissionSeeder 66 permissions):**

| Module | Actions |
|--------|---------|
| branches, divisions, employees, roles | view/create/update/delete |
| routers | view/create/update/delete/connect |
| servers | view/create/update/delete/connect/manage/provision/suspend/reset_password/delete_user |
| vendors, metro_ethernets, ip_transits, packages, services | view/create/update/delete |
| zabbix_monitors | view (hanya NOC+Owner/Admin) |
| clients, subscriptions, invoices, payments, financial_reports, mailboxes, tickets | varian view...verify |
| registrar_accounts | view/manage/test |
| domains | 12 actions (view/sync/link/register/renew/approve_renew/transfer/update_nameservers/manage_dns/manage_contacts/view_epp/set_epp) |
| notifications | view/manage/settings — **sudah ada** |
| system_updates, documentation, logs, settings | view/update |

**Role mapping aktual:**

| Role | Notifications | Maps |
|------|--------------|------|
| Owner | view+manage+settings (all) | — |
| Admin | view+manage+settings (minus roles/settings/logs delete_user) | — |
| Billing | view+manage | — |
| NOC | view+manage | — |
| CS/Sales/Finance/Employee | **tidak punya notifications.*** | — |
| Semua role | — | **maps.view belum ada** |

**Gap Fase 0:**
- `maps.view` (MVP) **wajib ditambahkan** additively tanpa `syncPermissions`. Kandidat: `maps.view` saja untuk MVP (bukan `network_maps.view` sampai layer infra).
- `dashboard.view` **tidak perlu** — semua `auth` boleh lihat dashboard, widget difilter `@can`.
- CS/Sales/Finance sengaja tidak punya `notifications.*` saat ini; header bell hidden. Keputusan Fase 1: tetap seperti ini atau berikan `notifications.view` ke semua (rekomendasi: berikan `view` ke semua yang punya `clients.view` agar inbox terlihat, `manage` tetap Owner/Admin/Billing/NOC).

## 3. Daftar Migration

**Sudah ada (relevan):**

- `0001_01_01_000000_create_users_table.php` + `180732_add_branch_and_division_to_users_table` + `020737_add_phone` + `021714_add_avatar`
- `2026_08_20_000004_create_admin_notifications_table.php` — schema: `user_id,target_role,type,title,message,payload(json),read_at,dismissed_at,expires_at` + indexes. **Belum ada** `category,severity,action_required,action_key,source_type,source_id,dedupe_key,resolved_at,resolved_by,snoozed_until`.
- `2026_01_28_041952_create_clients_table.php` (+ admin areas) — `latitude 10,8 longitude 11,8` **sudah ada**.
- `2026_08_06_110000_add_service_area_defaults_to_branches_table.php` — `default_latitude/longitude` **sudah ada**.
- `2026_08_20_000001_create_registrar_accounts_table.php`, `000002_create_registrar_operations_table.php` sudah ada.

**Butuh baru (Fase 0):**

| Migration | Kolom |
|-----------|-------|
| `2026_08_23_000001_add_dashboard_preferences_to_users_table.php` | `users.dashboard_preferences json nullable` |
| `2026_08_23_000002_add_notification_lifecycle_to_admin_notifications_table.php` | `category, severity, action_required bool default false, action_key nullable, source_type nullable, source_id nullable, dedupe_key nullable unique? + index, resolved_at nullable, resolved_by nullable FK users, snoozed_until nullable` + index `(source_type,source_id)`, `(dedupe_key)`, `(action_required, resolved_at)` |
| `map_assets` / `coverage_polygons` | **TUNDA** — MVP pakai existing `clients`/`branches` saja |

## 4. Daftar Pekerjaan Paralel (Boleh)

- `NotificationTypeRegistry` (Track A) ∥ `DashboardWidgetRegistry` (Track B) — setelah kontrak Fase 0.
- `DashboardStatsService` ∥ `OperationalMapService` (query + cache).
- UI dashboard ∥ UI map (jika endpoint contract `locations/summary` disepakati).
- Test & docs per track terpisah.
- Seed `maps.view` additively — koordinasikan sekali, jangan concurrent `PermissionSeeder` edit.

## 5. Daftar File yang Akan Disentuh (Per Track)

**Fase 0 (kontrak):**

- `app/Services/Admin/NotificationTypeRegistry.php` (baru)
- `app/Services/DashboardWidgetRegistry.php` (baru)
- `docs/plans/implementation-workflow-dashboard-notification-map.md` (jika keputusan berubah)

**Track A (Notification Core):**

- `database/migrations/*_add_notification_lifecycle_to_admin_notifications_table.php`
- `app/Models/AdminNotification.php` (fillable/casts/scopes: `resolved`, `snoozed`, `actionRequired`)
- `app/Services/Admin/AdminNotificationService.php` (API generik `notify(type, source, ctx)` + wrapper compatibility, `markResolved`, `snooze`, `dedupe_key`)
- `app/Http/Controllers/AdminNotificationController.php` (filter `category/severity/action_required/source`, resolver `action_key`)
- `resources/views/notifications/index.blade.php`, `show.blade.php` (redaksi payload, renderer per category, filter baru)
- `resources/views/layouts/header.blade.php` (polling count — jangan reload stats)
- `database/seeders/PermissionSeeder.php` (additive `maps.view`, jangan sync)
- `routes/web.php` (tambah `/operational-map` — koordinasi dengan Track C)

**Track B (Dashboard Foundation):**

- `database/migrations/*_add_dashboard_preferences_to_users_table.php`
- `app/Models/User.php` (`casts dashboard_preferences=>array`)
- `app/Services/DashboardWidgetRegistry.php`
- `app/Services/DashboardStatsService.php`
- `app/Http/Controllers/DashboardController.php` (baru, ganti closure)
- `app/View/Components/AppLayout.php` (breadcrumb dashboard)
- `resources/views/dashboard.blade.php` (ganti hardcode → real, grid 12, `@can`, empty "Belum ada data", periode per widget)
- `resources/views/components/dashboard/*.blade.php` (stat-card, chart-card)
- `routes/web.php` (`GET /dashboard`, `PUT /dashboard/preferences`)
- `public/assets/js/script.js` (Alpine `dashboardCustom()` + SortableJS)

**Track C (Operational Map MVP):**

- `app/Http/Controllers/OperationalMapController.php`
- `app/Services/OperationalMapService.php` (+ optional `MapLocationQueryService`)
- `resources/views/operational-map/index.blade.php`
- `resources/views/components/dashboard/operational-map.blade.php` (widget ringkas)
- `routes/web.php` (`/operational-map`, `/locations`, `/summary`)
- `database/seeders/PermissionSeeder.php` (sama file dengan Track A — checkpoint)
- `resources/views/layouts/sidebar.blade.php` (entry `Operational Map` di Infrastruktur/Pelanggan)
- `config/maps.php` (tidak ubah MVP), `app/Http/Controllers/MapLocationController.php` (reuse, tidak ubah)

**File konten bersama (JANGAN paralel tanpa koordinasi):**

- `PermissionSeeder.php`
- `routes/web.php` (blok `['auth','verified','ip.restrict']`)
- `header.blade.php` (bell + dashboard global)
- `public/assets/js/script.js` (fungsi global)
- `User.php` cast / `AdminNotification.php`

## 6. Keputusan Audience / Severity / Action / Source / Dedupe

**Source plan acuan:** `pusat-notifikasi-admin.md` §Status & Prinsip; `dashboard-customizable.md` §6.4 kontrak.

| Keputusan | Nilai |
|-----------|-------|
| `type` stabil | `domain_expiry`, `domain_overdue`, `registrar_offline`, `hosting_ssl_expiry`, `invoice_overdue`, `ticket_unassigned/high_priority`, `payment_verification`, `system_update_available` — variasi hari di payload bukan tipe baru. |
| `category` | `domain, hosting, billing, ticket, infrastructure, approval, system` |
| `severity` | `info, warning, high, critical` |
| `action_required` | true untuk overdue/sync_failed/conflict/registrar_offline/payment_verify/ticket_unassigned; false untuk reminder info |
| `action_key` | resolver server-side, bukan URL mentah: `view_domain, sync_domain, request_renew, view_invoice, verify_payment, view_ticket, view_map_filtered` |
| `source_type/source_id` | `SubscriptionDomain`, `Subscription` (hosting), `Invoice`, `Ticket`, `RegistrarAccount`, `Payment`, `HostingServer` |
| `dedupe_key` | `type:source_type:source_id:state` untuk incident; `type:domain_name:days_left` untuk expiry harian (existing). Satu incident aktif tidak spam harian. |
| Lifecycle | `unread (read_at null) → read (read_at) → dismissed (dismissed_at) | snoozed (snoozed_until) | resolved (resolved_at+resolved_by)` — `read != resolved`. |
| Audience resolver | user spesifik | role (Owner/Admin/Billing/NOC) | permission (`invoices.view`) | branch/division/queue | broadcast global `target_role null`. CTA re-check permission saat action. |
| Dashboard widget filter | hanya `action_required + unresolved + !dismissed + !snoozed` untuk Action Required; info masuk Inbox saja. |
| Payload redaksi | tidak pernah `auth_code_encrypted, provider_metadata, identity_number, password, token`. Hanya `domain_name, expires_at, days_left, client_code, error_summary` ter-redaksi. |

## 7. Isu / Konflik Terdeteksi

1. **Dashboard route closure** `routes/web.php:12` harus diganti sebelum Track B — single owner.
2. **`admin_notifications` payload raw** di `index/show` — bocor PII jika tidak redaksi; fix di Track A.
3. **Dedupe overdue spam** — `domain_overdue` buat 1 per hari per user; harus incident single aktif + escalation, bukan spam.
4. **Migration duplikat** `2026_02_13_040625` / `040630` metro_ethernet — cek sebelum `migrate:fresh` di CI.
5. **`User` cast vs `Up` migration** — 3 Track sentuh `User`/`AdminNotification`/`PermissionSeeder` → checkpoint.
6. **`CHANGELOG.md` belum mencatat workflow** — update di Fase 0 commit.

## 8. Kontrak Registry (Draft Final — disepakati di Fase 0)

### NotificationTypeRegistry (app/Services/Admin/NotificationTypeRegistry.php)

```php
'domain_expiry' => [
  'category'=>'domain','severity'=>'warning','action_required'=>true,
  'permission'=>'domains.view','action_key'=>'view_domain','dashboard'=>true,'ttl_days'=>30,
],
'invoice_overdue' => [
  'category'=>'billing','severity'=>'high','action_required'=>true,
  'permission'=>'invoices.view','action_key'=>'view_invoice','dashboard'=>true,'dedupe'=>'daily',
],
// + hosting_ssl_expiry, registrar_offline, ticket_unassigned, payment_verification, system_update_available, etc.
```

Resolver: `audience()` → `roles/permissions/branch`; `cta()` → cek `user->can(permission) && source exists` → route; `dedupeKey()` → `type:source_type:source_id:days_left`.

### DashboardWidgetRegistry (app/Services/DashboardWidgetRegistry.php)

```php
'clients_count' => ['title'=>'Total Pelanggan','permission'=>'clients.view','route'=>'clients.index','group'=>'Bisnis','w'=>3],
'outstanding_invoice' => ['title'=>'Outstanding','permission'=>'invoices.view','route'=>'invoices.index','group'=>'Keuangan'],
'zabbix_health' => ['title'=>'Kesehatan Zabbix','permission'=>'zabbix_monitors.view','route'=>'zabbix-monitors.index','group'=>'Infrastruktur','w'=>6],
'operational_map' => ['title'=>'Peta Operasional','permission'=>'maps.view','route'=>'operational-map.index','group'=>'Infrastruktur','w'=>6],
// + 13 lainnya sesuai docs/plans/dashboard-customizable.md
```

Default per role via `defaultForRole(User $user): array` — Owner full, Billing keuangan+sistem, NOC infra+tiket, dll.

## 9. Rencana Eksekusi Selanjutnya

1. Commit Fase 0 dokumen ini.
2. Eksekusi paralel: Track A (registry + lifecycle) ∥ Track B (DashboardController + stats) ∥ Track C (OperationalMap MVP) — mulai dengan migration additive & PermissionSeeder (checkpoint).
3. Jangan buat `NotificationTypeRegistry` dan `DashboardWidgetRegistry` commit bersamaan tanpa koordinasi (file berbeda — aman paralel).
4. Setiap track wajib `php -l`, `composer test` relevan, `permission/authorization test`, `graphify update`.
