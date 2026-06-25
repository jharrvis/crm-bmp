# Client Portal API - Dashboard

## Tujuan

Memberikan ringkasan data utama portal client setelah login.

## Authentication

Bearer token portal client wajib ada.

## Endpoint

### GET `/dashboard`

#### Response Success

Mengembalikan ringkasan seperti:

- data client
- jumlah langganan aktif
- invoice belum lunas
- tiket aktif
- notifikasi terbaru

## Dipakai Oleh

- halaman dashboard portal client

## Route Sumber

- `routes/client_portal_api.php`
- `App\Http\Controllers\Api\ClientPortal\ClientPortalDashboardController`
