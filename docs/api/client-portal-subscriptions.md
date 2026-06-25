# Client Portal API - Subscriptions

## Tujuan

Menampilkan daftar langganan client dan detail pemakaian layanan.

## Authentication

Bearer token portal client wajib ada.

## Endpoint

### GET `/subscriptions`

Mengembalikan daftar langganan milik client.

### GET `/subscriptions/{subscription}`

Mengembalikan detail satu langganan.

### GET `/subscriptions/{subscription}/usage`

Mengembalikan ringkasan penggunaan layanan jika tersedia.

### GET `/subscriptions/{subscription}/usage/chart`

Mengembalikan data chart penggunaan untuk ditampilkan di portal.

## Dipakai Oleh

- daftar layanan pelanggan di portal
- detail layanan
- widget penggunaan / monitoring

## Route Sumber

- `routes/client_portal_api.php`
- `App\Http\Controllers\Api\ClientPortal\ClientPortalSubscriptionController`
