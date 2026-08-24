# UAT Checklist — Integrasi Dashboard–Notification–Map

> Workflow §8 mensyaratkan UAT 5 role: Owner/Admin, Billing/Finance, NOC, CS, Sales.
> Polling hanya `notifications/count` tiap 60s — tidak reload stats dashboard.

## Setup
- `php artisan migrate` — `2026_08_23_000001` (notification lifecycle) + `000002` (dashboard_preferences)
- `php artisan db:seed --class=PermissionSeeder` — add `notifications.view` ke CS/Sales/Finance/Employee, `maps.view` ke NOC/Sales/CS/Employee, `maps.view` + `clients.view` dual gate
- `php artisan config:clear && php artisan view:clear`
- Buat 5 akun: `owner@test`, `billing@test`, `noc@test`, `cs@test`, `sales@test` — assign role sesuai `PermissionSeeder`

## Data Uji
- Cabang: `Branch` A (Salatiga) + B (Semarang) dengan `default_lat/lng`
- Pelanggan: 1 mapped (lat/lng valid, active), 1 unmapped (tanpa koordinat), 1 prospect — beda cabang
- Invoice: 1 overdue, 1 unpaid; Payment pending 1
- Ticket: 1 open high, 1 tanpa response >24j
- Domain: 1 `expires_at` +7 hari (untuk `domain_expiry`)
- Notifikasi: jalankan `CheckDomainExpiry`, `CheckHostingSslExpiry` atau `notify()` manual untuk `invoice_overdue`, `payment_verification`, `ticket_unassigned`, `registrar_offline`

## Checklist per Role

### Owner / Admin (semua permission)
- [ ] Dashboard tampil semua widget (18) termasuk Financial Attention, Operational Health, Peta Operasional
- [ ] Grid `w` preset berfungsi (3/4/6/8/12), drag posisi, reload persist, mobile fallback `col-span-12`
- [ ] Periode per widget (growth 7d/30d/1y, revenue 30d/1M/1y) mengubah angka (test 29 Jul vs 01 Aug membedakan)
- [ ] Notifikasi bell `refreshAdminNotifCount()` polling 60s — badge update tanpa reload stats
- [ ] Filter notifikasi `category=billing + infrastructure` + `action_required` tampil; CTA `view_map_filtered` cek `maps.view` → buka `/operational-map?branch_id/q`
- [ ] CTA `Tandai selesai` → `resolved_at` terisi, hilang dari Action Required, `read≠resolved`
- [ ] Peta: filter cabang/status/layanan/q/bbox konsisten antara `locations` (markers) dan `summary` (mapped/unmapped); popup hanya `id/name/client_code/status/city/branch/lat/lng/service` — tidak ada `identity_number/auth_code`
- [ ] Peta tanpa `maps.view` → 403; peta tanpa `clients.view` → 403

### Billing / Finance (`clients.view`, `invoices.*`, `payments.verify`, `financial_reports.view`, `notifications.view`)
- [ ] Dashboard: Outstanding, Revenue, Due, Pending Payments, Financial Attention tampil; Router/Server, Zabbix, Peta tidak tampil
- [ ] Financial Attention count = `billing` `actionRequired` (invoice_overdue + payment_verification)
- [ ] Revenue switch `30d` vs `1M` berbeda (100k vs 160k test)
- [ ] Notifikasi billing terlihat, infra tidak

### NOC (`zabbix_monitors.view`, `maps.view`, `routers/servers`, `domains`, `notifications.view+manage`)
- [ ] Dashboard: Zabbix Health, Router/Server, Domain Expiry, Registrar Health, Operational Health, Peta tampil
- [ ] Operational Health count = `infrastructure/system/domain/hosting` + `registrar_issues` hanya jika `registrar_accounts.view` (gate)
- [ ] Peta: markers pelanggan + cabang, clustering, popup, `locateMe`, attribution OSM
- [ ] Notifikasi infra `registrar_offline` → CTA `Test Koneksi` cek `registrar_accounts.view`

### CS (`clients.view`, `subscriptions.view`, `tickets.view`, `notifications.view`, `maps.view`)
- [ ] Dashboard: Total Pelanggan, Langganan, Tiket Terbuka/Belum Respon, Notifikasi, Peta tampil; Keuangan & infra detail tidak
- [ ] Tiket High Priority widget `High: n` merah
- [ ] Tombol `Tandai dibaca/selesai` **tidak** tampil (view-only) karena `@can('notifications.manage')`
- [ ] Peta filter `status=active` + `service` berfungsi

### Sales (`clients.view`, `subscriptions.view`, `services/packages.view`, `notifications.view`, `maps.view`)
- [ ] Dashboard: Total Pelanggan, Paket Terlaris, Pertumbuhan, Peta tampil; Keuangan tidak
- [ ] Paket Terlaris Top 5 tampil (perlu `packages.view`)
- [ ] Peta mode marketing: `prospect` vs `active`, heatmap belum (coverage ditunda)

## Negative / Edge
- [ ] Dashboard `w` invalid (5, 99) → 422 atau clamp ke `default_w` (test `test_widget_w_preset_validation_and_clamp`)
- [ ] `router_server` visible jika punya **salah satu** `routers.view` OR `servers.view` (test `test_router_server_visible_if_either_permission`)
- [ ] Summary `mapped=unmapped` → `mapped=0`, `unmapped=total` konsisten
- [ ] `q` dengan secret `password` → payload redacted tidak tampil di UI (test `test_payload_recursive_redaction`)
- [ ] Tanpa data → semua widget `Belum ada data`, peta empty state

## Hasil UAT
| Role | Tanggal | Penguji | Hasil | Catatan |
|------|---------|---------|-------|---------|
| Owner | | | PASS/FAIL | |
| Billing | | | PASS/FAIL | |
| NOC | | | PASS/FAIL | |
| CS | | | PASS/FAIL | |
| Sales | | | PASS/FAIL | |

> Setelah lulus, tandai workflow §8 selesai dan lanjut Network Coverage setelah koordinat router/POP/tower/ODP tervalidasi.
