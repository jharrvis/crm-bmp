# CRM BMP.NET

CRM internal berbasis Laravel untuk operasional BMP.NET, mencakup pengelolaan pelanggan, langganan layanan, invoice, tiket, branch, dan portal client.

## Fitur Utama

- Manajemen pelanggan per cabang dengan format `client_code` baru berbasis `branch_id + tahun + nomor urut`
- Manajemen langganan internet, hosting, domain, dan detail teknis koneksi
- **Manage Server Web Hosting (HestiaCP)**: konsol operasional (snapshot, daftar user live, test koneksi) serta provisioning/lifecycle akun (buat, link, suspend/activate, reset password, hapus) melalui queue.
- **Mail Hosting** terintegrasi Zimbra dengan manajemen mailbox dan provisioning berbasis queue.
- Billing langganan dengan opsi `PPN 11%` dan `PPh23 2%`
- Generate invoice otomatis dari langganan aktif
- Template invoice dengan alamat branch, breakdown pajak, dan terbilang bahasa Indonesia
- Seeder import pelanggan untuk cabang tertentu
- Portal client API untuk invoice, dashboard, dan subscription

## Stack

- Laravel
- Blade + Tailwind CSS
- MySQL
- JavaScript untuk interaksi admin panel

## Menjalankan Project

1. Install dependency:

```bash
composer install
npm install
```

2. Copy environment dan generate app key:

```bash
cp .env.example .env
php artisan key:generate
```

3. Atur koneksi database di `.env`, lalu jalankan:

```bash
php artisan migrate
php artisan db:seed
```

4. Jalankan aplikasi:

```bash
php artisan serve
npm run dev
```

## Changelog

Ringkasan perubahan tersedia di [CHANGELOG.md](CHANGELOG.md).

Untuk rilis di GitHub, gunakan isi `CHANGELOG.md` sebagai dasar catatan release agar riwayat perubahan tetap konsisten.
