# Client Portal API - Invoices

## Tujuan

Menampilkan daftar invoice client dan akses detail invoice.

## Authentication

Bearer token portal client wajib ada.

## Endpoint

### GET `/invoices`

Mengembalikan daftar invoice milik client.

### GET `/invoices/{invoice}`

Mengembalikan detail invoice tertentu.

### GET `/invoices/{invoice}/download`

Mengunduh file invoice yang dapat dibaca/diarsipkan client.

## Dipakai Oleh

- halaman daftar tagihan portal
- halaman detail tagihan
- fitur download invoice

## Route Sumber

- `routes/client_portal_api.php`
- `App\Http\Controllers\Api\ClientPortal\ClientPortalInvoiceController`
