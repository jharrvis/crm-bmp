# Changelog

Semua perubahan penting pada project ini dicatat di file ini.

## 2026-06-25

### Added

- Entry point `global search` di header aplikasi untuk pencarian lintas modul
- Halaman `Pembaruan Sistem` yang membaca `CHANGELOG.md` dan menampilkan commit terbaru repo lokal
- Halaman `Activity Log` global berbasis `spatie/laravel-activitylog`
- Placeholder halaman `Pengaturan` untuk rencana konfigurasi sistem ke depan
- Group menu baru `Sistem` di sidebar

### Changed

- `Manajemen Role` dipindahkan ke group menu `Sistem`
- Struktur permission role dikelompokkan ulang agar modul `Sistem` tampil konsisten di halaman edit role
- Sidebar disusun ulang agar menu `Pembaruan Sistem`, `Activity Log`, `Manajemen Role`, dan `Pengaturan` berada dalam satu kelompok administratif

### Fixed

- Inkonsistensi penempatan menu administratif yang sebelumnya tersebar di `Master Data`

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
