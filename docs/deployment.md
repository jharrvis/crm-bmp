# Deployment Notes

## Prinsip Umum

Setiap deploy harus mengecek apakah perubahan menyentuh:

- migration
- seeder
- permission/role
- cache config/view/route
- environment variable

## Checklist Ringkas

1. Pull branch target
2. Jalankan migration jika ada
3. Jalankan seeder jika ada permission atau data referensi baru
4. Clear cache Laravel jika menyentuh config/view
5. Verifikasi halaman utama yang terdampak

## Command yang Sering Dipakai

```bash
php artisan migrate
php artisan db:seed --class=PermissionSeeder
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

## Catatan Khusus

- Jika ada perubahan `config/app.php` atau `.env`, wajib jalankan `php artisan config:clear`.
- Jika ada permission baru, jalankan `PermissionSeeder` lalu `php artisan permission:cache-reset`. Seeder hanya menambahkan permission default dan tidak mencabut permission role yang telah diatur di production.
- Untuk koreksi prefix kode pelanggan, backup database terlebih dahulu lalu jalankan `php artisan clients:reconcile-codes --branch={id} --dry-run`. Command tidak mengubah data pada mode default. Bila prefix target sudah dipakai, command mengalokasikan nomor urut kosong berikutnya tanpa menimpa kode yang ada; gunakan `--apply --confirm` hanya setelah laporan audit diverifikasi.
- Jika ada rewrite history Git, server production tidak boleh memakai `git pull` biasa. Gunakan sinkronisasi branch yang sesuai.
- Jika ada migration invoice manual baru, jalankan `php artisan migrate`.
- Jika update mencakup default wilayah cabang atau modal peta pelanggan, jalankan `php artisan migrate`. Tidak diperlukan API key Google Maps.
- Jika update mencakup pencarian lokasi pelanggan atau modul IP Transit, jalankan `php artisan migrate`, `php artisan db:seed --class=PermissionSeeder`, lalu `php artisan permission:cache-reset`.
- Untuk pencarian lokasi, set `MAP_NOMINATIM_USER_AGENT` di `.env` dengan nama aplikasi dan kontak operasional. Pencarian menggunakan Nominatim/OpenStreetMap dari server, tidak memerlukan API key atau billing account.
- Jika form atau halaman invoice menambah class Tailwind baru di Blade, jalankan `npm run build`.
- Jika fitur signature invoice dipakai di server baru, pastikan `php artisan storage:link` sudah tersedia agar file di disk `public` dapat diakses.
- Untuk Manage Server HestiaCP (modul Web Hosting): jalankan `php artisan migrate`, `php artisan db:seed --class=PermissionSeeder`, `php artisan permission:cache-reset`, `php artisan optimize:clear`, lalu `php artisan queue:restart`. Pastikan queue worker berjalan karena seluruh operasi remote (provision, suspend, reset password, delete, refresh snapshot) diproses sebagai job. Set Hestia access/secret key minimum dan whitelist IP server CRM sebelum mengaktifkan provisioning. Konfigurasi TLS opsional via `HESTIACP_VERIFY_SSL` di `.env`. Akun hosting lama tidak otomatis memperoleh lifecycle access setelah migration; hanya akun dengan bukti provisioning CRM yang dapat diubah atau dihapus.
