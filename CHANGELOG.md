# Changelog

Semua perubahan penting pada project ini dicatat di file ini.

## 2026-07-27

### Added

- Halaman detail Router menampilkan identitas perangkat, kredensial yang dapat ditampilkan/disalin, serta daftar langganan yang terhubung. Hasil Router di Pencarian Global sekarang langsung membuka halaman ini.

### Changed

- Form tambah dan edit Router sekarang menyediakan kontrol untuk menampilkan/menyembunyikan serta menyalin password router, tanpa menyimpan password ke Activity Log.
- Daftar Router sekarang menyediakan aksi lihat detail sebelum edit dan hapus.
- Modal Router diperlebar dan ditata dalam grid dua kolom pada desktop agar seluruh form dan aksi simpan lebih mudah dijangkau tanpa scroll panjang.

## 2026-07-10

### Changed

- Fitur register user publik sekarang dinonaktifkan secara default karena akun staff dan client hanya dibuat oleh admin.
- Route, halaman, dan link UI register sekarang tidak bisa diakses saat `AUTH_REGISTRATION_ENABLED=false`, tetapi implementasinya tetap disimpan untuk kebutuhan aktivasi di masa depan.

## 2026-07-01

### Added
- **Sistem Pengingat Tagihan Otomatis** (`InvoiceReminder`): Mengirim email peringatan sebelum jatuh tempo dan pemberitahuan overdue secara otomatis setiap jam 08:00 pagi.
- Jadwal reminder mengikuti pengaturan global (`billing.reminder_days_before` dan `billing.reminder_days_after`).
- **PDF Invoice**: Download PDF invoice via DomPDF, dengan template khusus `invoices/pdf.blade.php` yang kompatibel untuk cetak A4.
- **Client Portal Payment Confirmation**: Client dapat mengirim konfirmasi pembayaran via endpoint API portal.
- **Laporan Keuangan** (`FinancialReportController`): Halaman ringkasan pendapatan, tagihan, piutang, aging report, dan daftar pembayaran terbaru per periode.
- Menu sidebar **Laporan Keuangan** untuk akses cepat ke halaman report.

### Changed
- **Email Queue**: Pengiriman email tagihan sekarang dilakukan secara asynchronous melalui queue untuk mencegah proses request yang lama/blocking.
- **Normalisasi WhatsApp**: Nomor WhatsApp tujuan sekarang dinormalisasi (mengubah awalan `0` menjadi `62`) agar API web `wa.me` berfungsi dengan baik saat membuka link chat.
- **Invoice Delivery Mail** sekarang menggunakan `ShouldQueue` untuk pengiriman asynchronous.
- **SystemSetting** menggantikan helper `setting()` — semua pemanggilan di controller, model, job, dan blade view menggunakan `\App\Models\SystemSetting::get()`.

### Fixed
- Race condition pada `generateInvoiceNumber()`: sekarang menggunakan database transaction + `lockForUpdate()`.
- Helper `setting()` tidak reliable di production (error autoload), diganti dengan method statis SystemSetting.

### Deployment Notes
- Jalankan `composer update` untuk menginstal `barryvdh/laravel-dompdf`.
- Jalankan `php artisan migrate` untuk tabel `invoice_reminders`.
- Jalankan `php artisan queue:restart` jika queue worker sedang berjalan.
- Konfigurasi queue worker untuk production: `php artisan queue:work`.

## 2026-06-30

### Added

- **Sistem Pengaturan Global** (`SystemSettings`): konfigurasi yang bisa diubah via UI untuk billing (PPN, PPh23, due days, tanggal generate, prorata toggle, reminder schedule).
- **Modul Pembayaran** (`Payments`): mencatat pembayaran untuk tagihan. Mendukung status `pending`, `verified`, dan `rejected`.
- **Status Invoice `partially_paid`**: status baru saat pembayaran kurang dari total tagihan.
- **Prorata Kalkulator**: penghitungan proporsional untuk tagihan saat registrasi baru, upgrade/downgrade, dan penghentian layanan di tengah siklus bulan.
- **Job Auto Generate Tagihan Bulanan**: cron job (`GenerateMonthlyInvoices`) yang otomatis membuat tagihan setiap bulan pada tanggal yang ditentukan di pengaturan.
- **Job Auto Overdue**: cron job (`MarkOverdueInvoices`) yang menandai status `overdue` secara otomatis.

### Changed

- Tarif pajak (PPN dan PPh23) tidak lagi hardcoded, melainkan mengambil nilai dari `SystemSettings`.
- Data tax breakdown sekarang disimpan pada saat tagihan bulanan dibuat secara otomatis, sehingga item tagihan konsisten dengan total tagihan.
- Penomoran tagihan sekarang mendukung lock level database (transaction lock for update) untuk mencegah nomor kembar.
- Halaman daftar tagihan sekarang di-load menggunakan server-side pagination, tidak lagi meload seluruh data sekaligus.

## 2026-06-29

### Changed

- `AGENTS.md` ditambahkan Section 19: Standar Keamanan Kode, mencakup input/output escaping, authentication & authorization, data protection & enkripsi, secret management, API security, file upload, dependency security, dan mass assignment protection.

## 2026-06-29

### Changed

- `AGENTS.md` disempurnakan: tambah standar kode teknis (naming convention, arsitektur, validation, error handling), standar queue/job, standar testing, standar branching, standar environment variables. Numbering section diperbaiki. Daftar modul disinkronkan dengan kode aktual. Referensi skill operasional diupdate dengan lokasi yang benar. Tambah section known permission gaps.
- `docs/README.md` diupdate dengan index modul lengkap.

### Added

- Dokumentasi modul `Clients` di `docs/modules/clients.md`: data pelanggan, kontak PIC, portal account, alur bisnis, permission matrix.
- Dokumentasi modul `Subscriptions` di `docs/modules/subscriptions.md`: langganan connectivity/hosting/domain, pricing model, integrasi Zabbix/HestiaCP, known issues.
- Dokumentasi modul `Tickets` di `docs/modules/tickets.md`: tiket support, queue system, canned response, SLA tracking, bulk update, integrasi client portal.
- Port 5 CRM skills ke opencode (`crm-doc-maintainer`, `crm-release-checker`, `crm-api-doc-writer`, `crm-permission-auditor`, `crm-activitylog-auditor`).

## 2026-06-27

### Added

- Fitur upload foto profil dengan preview sebelum simpan di halaman profil
- Tab `Tagihan` di halaman detail pelanggan dengan ringkasan dan daftar invoice terkait, dilindungi permission `invoices.view`

### Changed

- Halaman daftar role sekarang hanya menampilkan jumlah user per role, bukan daftar user. Daftar user lengkap tetap tersedia di halaman detail role.
- Thread percakapan pada halaman detail ticket sekarang ditampilkan sebagai bubble chat kanan/kiri untuk membedakan pesan client, staff, dan internal note dengan lebih jelas.

## 2026-06-26

### Added

- Halaman khusus `Buat Invoice Manual` sebagai pengganti drawer/modal di daftar invoice
- Modul dokumentasi `Invoices` di `docs/modules/invoices.md`
- Dokumentasi modul `Activity Log` di `docs/modules/activity-log.md`

### Changed

- Tombol `Buat Invoice Manual` di daftar invoice sekarang menuju halaman create khusus
- Halaman daftar invoice difokuskan kembali hanya untuk overview, filter, tabel, dan aksi invoice
- Form invoice manual sekarang mendukung pencarian pelanggan, tanggal invoice, preset jatuh tempo, ringkasan subtotal/PPN/diskon, tanda tangan, draft/final/send action, dan modal kirim email/WhatsApp
- Form invoice manual sekarang menempatkan catatan di kiri, ringkasan tagihan di kanan, serta area tanda tangan tepat di atas tombol simpan
- Daftar invoice sekarang menampilkan status draft, indikator invoice sudah terkirim atau belum, serta aksi edit, kirim ulang, print, dan download PDF dari dropdown aksi
- Pembuatan dan update invoice yang memilih kanal WhatsApp sekarang kembali ke daftar invoice lalu langsung membuka link `wa.me`
- Halaman detail invoice sekarang dapat menampilkan tanda tangan dan mendukung mode print otomatis

### Documentation

- TODO dokumentasi sekarang mencakup metode penghapusan `activity log` berkala menggunakan `php artisan activitylog:clean`

### Deployment Notes

- Jalankan `php artisan migrate` untuk field invoice manual baru
- Jalankan `npm run build` jika deploy memakai aset hasil build Tailwind
- Pastikan `php artisan storage:link` tersedia bila signature invoice diakses dari disk `public`

## 2026-06-25

### Added

- Entry point `global search` di header aplikasi untuk pencarian lintas modul
- Halaman `Pembaruan Sistem` yang membaca `CHANGELOG.md` dan menampilkan commit terbaru dari GitHub repo
- Halaman `Activity Log` global berbasis `spatie/laravel-activitylog`
- Halaman `Dokumentasi` di dalam aplikasi yang membaca file markdown dari folder `docs/`
- Struktur awal dokumentasi `docs/` untuk deployment, permission matrix, modul, dan API portal client
- Placeholder halaman `Pengaturan` untuk rencana konfigurasi sistem ke depan
- Group menu baru `Sistem` di sidebar
- Standar kerja repo melalui `AGENTS.md` untuk dokumentasi, changelog, permission, deployment note, dan API

### Changed

- `Manajemen Role` dipindahkan ke group menu `Sistem`
- Struktur permission role dikelompokkan ulang agar modul `Sistem` tampil konsisten di halaman edit role
- Sidebar disusun ulang agar menu `Pembaruan Sistem`, `Dokumentasi`, `Activity Log`, `Manajemen Role`, dan `Pengaturan` berada dalam satu kelompok administratif
- Role management sekarang dapat menampilkan siapa saja user yang memakai setiap role
- Timezone aplikasi sekarang mengikuti `APP_TIMEZONE` agar activity log dan timestamp internal bisa memakai region yang benar
- Global search diperluas ke hampir semua modul data yang dapat diakses user, termasuk organisasi, pelanggan, billing, support, infrastruktur, layanan, dan paket
- Global search sekarang memiliki halaman hasil pencarian penuh dengan filter modul dan link `Lihat semua hasil` dari dropdown header
- Global search sekarang mendukung `quick view` untuk modul operasional seperti pelanggan, router, server, vendor, metro ethernet, cabang, divisi, karyawan, layanan, dan paket tanpa perlu pindah halaman

### Fixed

- Inkonsistensi penempatan menu administratif yang sebelumnya tersebar di `Master Data`
- Jam pada aplikasi yang sebelumnya selalu mengikuti `UTC` karena timezone config masih hardcoded
- Legacy role `Client` dan user dummy `Pelanggan A` yang sebelumnya muncul di manajemen role walaupun portal client sudah memakai tabel akun terpisah
- Overlay modal sekarang dipindahkan ke `document.body` agar backdrop benar-benar fullscreen dan tidak bergeser oleh container layout

## 2026-06-24

### Added

- Seeder akun `webmaster@bmp.net.id` dengan role `Owner` untuk akses penuh sistem
- Permission baru untuk modul infrastruktur: `vendors`, `metro_ethernets`, dan `zabbix_monitors`
- Asset dekoratif SVG untuk panel kanan halaman auth

### Changed

- Halaman `login` dan `forgot password` didesain ulang ke layout split dua kolom yang lebih modern
- Panel kanan auth disesuaikan agar lebih dekat ke referensi TailAdmin, dengan latar biru tua dan grid dekoratif
- Manajemen role diselaraskan dengan visibilitas menu dan akses route berbasis permission
- Default permission role `NOC` diperluas agar dapat melihat modul infrastruktur yang relevan

### Fixed

- Menu `Infrastruktur` yang sebelumnya tidak muncul untuk role `NOC` walaupun permission sudah ada
- Inkonsistensi modul `Organisasi`, `Infrastruktur`, dan `Manajemen Role` yang sebelumnya masih dikunci berdasarkan nama role

## 2026-05-16

### Added

- Toggle `PPh23 2%` pada billing langganan
- `registered_at` untuk pelanggan
- Seeder import pelanggan internet cabang Kudus
- Breakdown pajak dan terbilang bahasa Indonesia pada invoice

### Changed

- Format `client_code` pelanggan diubah ke `{branch_id}{yy}{sequence}`
- Form input/edit layanan dipecah menjadi tab `Umum`, `Billing`, dan `Teknis`
- Layout invoice disesuaikan mengikuti format operasional BMP.NET
- Footer invoice dan mode print dioptimalkan agar lebih rapi di kertas A4

### Fixed

- Animasi toggle pajak pada form layanan
- Label pajak pada ringkasan billing
- Terbilang invoice yang sebelumnya menambahkan `nol` di akhir nominal bulat
- Invoice cabang yang sebelumnya masih memakai alamat hardcoded

## 2026-05-15

### Added

- Seeder backfill `client_code` pelanggan existing
- Logika generate kode pelanggan baru berbasis branch dan tahun registrasi
- Opsi `PPN 11%` pada langganan

### Changed

- Generator invoice otomatis memakai harga dasar layanan untuk item invoice baru agar breakdown pajak lebih konsisten

### Fixed

- Sinkronisasi total tagihan `harga jual + PPN - PPh23`
