# Client Portal API Overview

## Tujuan

API ini dipakai oleh aplikasi portal client untuk:

- login OTP
- melihat dashboard ringkas pelanggan
- melihat langganan
- melihat invoice
- membuat dan memantau tiket
- membaca notifikasi

## Authentication

Portal client memakai token session sendiri, bukan session login staff CRM.

- OTP diminta dengan email akun portal
- verifikasi OTP menghasilkan token
- request setelah login memakai header:

```http
Authorization: Bearer {token}
```

## Base Group

Semua endpoint berada di grup route `client_portal_api.php`.

## Modul Endpoint

- `client-portal-auth.md`
- `client-portal-dashboard.md`
- `client-portal-subscriptions.md`
- `client-portal-invoices.md`
- `client-portal-tickets.md`
- `client-portal-notifications.md`

## Catatan

- Jika endpoint berubah, dokumentasi ini dan dokumen modul terkait wajib ikut diperbarui.
