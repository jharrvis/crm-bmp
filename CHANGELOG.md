# Changelog

Semua perubahan penting pada project ini dicatat di file ini.

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
