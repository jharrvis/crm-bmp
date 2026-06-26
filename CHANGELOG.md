# Changelog

Semua perubahan penting pada project ini dicatat di file ini.

## 2026-06-26

### Added

- Halaman khusus `Buat Invoice Manual` sebagai pengganti drawer/modal di daftar invoice
- Modul dokumentasi `Invoices` di `docs/modules/invoices.md`
- Dokumentasi modul `Activity Log` di `docs/modules/activity-log.md`

### Changed

- Tombol `Buat Invoice Manual` di daftar invoice sekarang menuju halaman create khusus
- Halaman daftar invoice difokuskan kembali hanya untuk overview, filter, tabel, dan aksi invoice
- Form invoice manual sekarang mendukung pencarian pelanggan, tanggal invoice, preset jatuh tempo, ringkasan subtotal/PPN/diskon, tanda tangan, draft/final/send action, dan modal kirim email/WhatsApp
- Daftar invoice sekarang menampilkan status draft dan indikator invoice sudah terkirim atau belum
- Halaman detail invoice sekarang dapat menampilkan tanda tangan dan membuka link WhatsApp setelah aksi kirim
- Form invoice manual sekarang menempatkan catatan di kiri, ringkasan tagihan di kanan, serta area tanda tangan tepat di atas tombol simpan
- Daftar invoice sekarang memiliki aksi edit, kirim ulang, print, dan download PDF dari dropdown aksi
- Pembuatan dan update invoice yang memilih kanal WhatsApp sekarang kembali ke daftar invoice lalu langsung membuka link `wa.me`

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
