# Client Portal API - Authentication

## Tujuan

Menangani proses login portal client berbasis OTP email.

## Authentication

- `request-otp` dan `verify-otp` tidak butuh token
- endpoint lain di file ini butuh bearer token portal client

## Endpoint

### POST `/auth/request-otp`

Meminta OTP untuk email akun portal client.

#### Request

```json
{
  "email": "client@example.com"
}
```

#### Response Success

```json
{
  "message": "OTP berhasil dikirim.",
  "expires_in_minutes": 10
}
```

#### Response Error

- email tidak terdaftar
- rate limit OTP terlampaui

### POST `/auth/verify-otp`

Memverifikasi OTP dan mengembalikan token session portal.

#### Request

```json
{
  "email": "client@example.com",
  "otp": "123456"
}
```

#### Response Success

```json
{
  "token": "plain-text-token",
  "token_type": "Bearer",
  "expires_at": "2026-06-25T10:00:00+07:00",
  "account": {}
}
```

#### Response Error

- OTP tidak valid
- OTP kedaluwarsa
- jumlah percobaan OTP melebihi batas

### POST `/auth/logout`

Mencabut session token aktif.

### GET `/auth/me`

Mengembalikan profil akun portal client yang sedang login.

## Dipakai Oleh

- halaman login portal client
- bootstrap session portal client

## Route Sumber

- `routes/client_portal_api.php`
- `App\Http\Controllers\Api\ClientPortal\ClientPortalAuthController`
