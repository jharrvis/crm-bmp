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

## Catatan

- Sidebar harus mengikuti permission, bukan nama role hardcoded.
- Jika modul baru ditambahkan, permission-nya harus ikut didaftarkan di `PermissionSeeder` dan dimunculkan di `Manajemen Role`.
