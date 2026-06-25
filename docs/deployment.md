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
- Jika ada permission baru, jalankan `PermissionSeeder` lalu `php artisan permission:cache-reset`.
- Jika ada rewrite history Git, server production tidak boleh memakai `git pull` biasa. Gunakan sinkronisasi branch yang sesuai.
