# Client Portal Next.js

Scaffold awal untuk portal client PWA yang terhubung ke API CRM.

## Tujuan tahap ini

- login Email OTP ke API CRM
- menyimpan token auth CRM di HTTP-only cookie
- memuat dashboard dasar dari endpoint CRM

## Endpoint CRM yang dipakai

- `POST /api/client-portal/auth/request-otp`
- `POST /api/client-portal/auth/verify-otp`
- `POST /api/client-portal/auth/logout`
- `GET /api/client-portal/auth/me`
- `GET /api/client-portal/dashboard`
- `GET /api/client-portal/subscriptions`
- `GET /api/client-portal/invoices`
- `GET /api/client-portal/notifications`
- `GET /api/client-portal/tickets`

## Menjalankan app

1. Copy `.env.example` menjadi `.env.local`
2. Isi `CRM_API_BASE_URL`
3. Install dependency
4. Jalankan `npm run dev`

## Catatan

- Folder ini belum di-install dependency-nya.
- Scaffold ini sengaja tipis dan fokus ke fondasi auth + fetch API.
