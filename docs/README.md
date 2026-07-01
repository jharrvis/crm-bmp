# Dokumentasi BMPnet CRM

Dokumentasi ini menjadi sumber referensi utama untuk:

- modul aplikasi
- endpoint API
- deployment note
- permission matrix
- standar pengembangan

## Struktur

- `api/`
  - dokumentasi endpoint, terutama untuk integrasi Client Portal
- `modules/`
  - dokumentasi modul internal CRM
  - `activity-log.md` — Activity Log dan TODO operasionalnya
  - `clients.md` — Manajemen pelanggan, kontak PIC, dan portal account
  - `invoices.md` — Alur manual invoice, pengiriman, dan signature
  - `payments.md` — Pencatatan pembayaran, verifikasi, dan status tagihan
  - `financial-reports.md` — Laporan keuangan, aging report, dan ringkasan pendapatan
  - `role-management.md` — CRUD role, permission sync, dan integrasi sidebar
  - `subscriptions.md` — Langganan internet/hosting/domain, pricing model, dan integrasi teknis
  - `tickets.md` — Tiket support, queue, canned response, dan integrasi client portal
- `deployment.md`
  - catatan deployment, cache clear, migration, dan seeder
- `permission-matrix.md`
  - mapping menu, permission, dan role default

## Prinsip

1. Dokumentasi ini adalah sumber kebenaran yang dibaca dari repo.
2. Halaman `Dokumentasi` di aplikasi hanya menampilkan isi folder ini.
3. Setiap perubahan fitur yang penting harus mengupdate dokumen terkait.
4. Perubahan API wajib mengupdate file di `docs/api/`.
