# Permission Matrix

## Tujuan

Dokumen ini merangkum hubungan antara menu, permission, dan role default.

## Sistem

### Pembaruan Sistem

- Permission: `system_updates.view`
- Role default:
  - Owner
  - Admin
  - Employee
  - Billing
  - NOC
  - CS
  - Sales
  - Finance

### Dokumentasi

- Permission: `documentation.view`
- Role default:
  - Owner
  - Admin
  - Employee
  - Billing
  - NOC
  - CS
  - Sales
  - Finance

### Activity Log

- Permission: `logs.view`
- Role default:
  - Owner
  - Admin

### Manajemen Role

- Permission:
  - `roles.view`
  - `roles.create`
  - `roles.update`
  - `roles.delete`
- Role default:
  - Owner
  - Admin

## Operasional

### Pelanggan

- Permission utama: `clients.view`

### Langganan

- Permission utama: `subscriptions.view`

### Tagihan

- Permission utama: `invoices.view`

### Tiket

- Permission utama: `tickets.view`

## Infrastruktur

### Server Web Hosting (HestiaCP)

- Permission:
  - `servers.view` - melihat daftar server
  - `servers.connect` - test koneksi dan data live
  - `servers.manage` - melihat halaman manage dan daftar user
  - `servers.provision` - membuat/link user dan domain
  - `servers.suspend` - suspend/activate akun
  - `servers.reset_password` - reset password akun
  - `servers.delete_user` - hapus user remote
- Role default:
  - Owner (semua)
  - Admin (semua)
  - NOC (`servers.connect`, `servers.manage`, `servers.suspend`; tanpa `servers.delete_user`)

### IP Transit

- Permission:
  - `ip_transits.view`
  - `ip_transits.create`
  - `ip_transits.update`
  - `ip_transits.delete`
- Role default:
  - Owner
  - Admin
  - NOC

## Catatan

- Sidebar harus mengikuti permission, bukan nama role hardcoded.
- Jika modul baru ditambahkan, permission-nya harus ikut didaftarkan di `PermissionSeeder` dan dimunculkan di `Manajemen Role`.
