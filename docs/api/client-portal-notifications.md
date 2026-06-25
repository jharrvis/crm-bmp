# Client Portal API - Notifications

## Tujuan

Menampilkan dan menandai notifikasi portal client.

## Authentication

Bearer token portal client wajib ada.

## Endpoint

### GET `/notifications`

Mengembalikan daftar notifikasi client.

### POST `/notifications/{notification}/read`

Menandai satu notifikasi sebagai dibaca.

### POST `/notifications/read-all`

Menandai semua notifikasi client sebagai dibaca.

## Dipakai Oleh

- inbox/notifikasi portal client
- badge unread notification

## Route Sumber

- `routes/client_portal_api.php`
- `App\Http\Controllers\Api\ClientPortal\ClientPortalNotificationController`
