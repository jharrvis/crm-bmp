# Client Portal API - Tickets

## Tujuan

Mengelola tiket support dari portal client.

## Authentication

Bearer token portal client wajib ada.

## Endpoint

### GET `/tickets`

Mengembalikan daftar tiket milik client.

### POST `/tickets`

Membuat tiket baru dari portal client.

### GET `/tickets/{ticket}`

Mengembalikan detail tiket dan balasan terkait.

### POST `/tickets/{ticket}/reopen`

Membuka kembali tiket yang sudah ditutup jika diizinkan alur bisnis.

### POST `/tickets/{ticket}/replies`

Menambahkan balasan pada tiket.

## Dipakai Oleh

- daftar tiket portal
- pembuatan tiket baru
- timeline percakapan tiket

## Route Sumber

- `routes/client_portal_api.php`
- `App\Http\Controllers\Api\ClientPortal\ClientPortalTicketController`
