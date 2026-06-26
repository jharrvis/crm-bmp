# Activity Log

## Tujuan

Menyediakan jejak audit untuk aktivitas penting user dan perubahan data utama di CRM.

## Entitas Terkait

- `activity_log`
- `User`
- entitas utama yang memakai trait logging model

## Route Utama

- `GET /activity-logs`

## Permission

- `logs.view`

## Alur Bisnis

1. Aksi penting seperti login, logout, create, update, delete, dan perubahan role dicatat ke tabel `activity_log`.
2. Halaman `Activity Log` menampilkan filter dasar berdasarkan log name, event, dan causer.
3. Timestamp mengikuti timezone aplikasi melalui `APP_TIMEZONE`.

## Penyimpanan

- Data disimpan di tabel database `activity_log`.
- Konfigurasi package saat ini menyetel `delete_records_older_than_days = 365`.
- Cleanup belum berjalan otomatis jika command pembersihan tidak dijadwalkan.

## TODO

- Tambahkan prosedur penghapusan activity log berkala dengan command `php artisan activitylog:clean`.
- Tambahkan scheduler atau cron production untuk cleanup otomatis sesuai kebijakan retensi.
- Dokumentasikan kebijakan retensi final apakah tetap `365` hari atau disesuaikan per kebutuhan operasional.

## Catatan

- Log sensitif seperti password, secret key, dan field rahasia lain harus tetap dikecualikan.
- Jika coverage log bertambah, dokumentasi modul ini harus ikut diperbarui.
