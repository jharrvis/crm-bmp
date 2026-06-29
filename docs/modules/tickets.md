# Tickets / Tiket Support

## Tujuan

Sistem tiket support pelanggan dengan fitur assignment, queue, prioritas, balasan (bubble chat), catatan internal, attachment, canned response, bulk update, dan integrasi client portal. Mendukung tracking SLA melalui field `first_response_at`, `resolved_at`, dan `closed_at`.

## Entitas Terkait

| Model | File | Keterangan |
|-------|------|------------|
| `Ticket` | `app/Models/Ticket.php` | Entitas utama tiket |
| `TicketReply` | `app/Models/TicketReply.php` | Balasan tiket (staff/client) |
| `TicketReplyAttachment` | `app/Models/TicketReplyAttachment.php` | File attachment per reply |
| `TicketActivity` | `app/Models/TicketActivity.php` | Activity log khusus tiket |
| `TicketCannedResponse` | `app/Models/TicketCannedResponse.php` | Template balasan cepat |

### Service Classes

| Service | File | Keterangan |
|---------|------|------------|
| `TicketActivityService` | `app/Services/TicketActivityService.php` | Pencatatan aktivitas tiket |
| `TicketNotificationService` | `app/Services/TicketNotificationService.php` | Notifikasi tiket ke client portal |
| `TicketCannedResponseRenderer` | `app/Services/TicketCannedResponseRenderer.php` | Render template canned response dengan placeholder |

### Relasi Ticket

- `client()` → belongsTo `Client`
- `subscription()` → belongsTo `Subscription` (opsional)
- `createdByPortalAccount()` → belongsTo `ClientPortalAccount`
- `assignedUser()` → belongsTo `User` (staff yang di-assign)
- `replies()` → hasMany `TicketReply`
- `activities()` → hasMany `TicketActivity`

### Field Ticket

`client_id`, `subscription_id`, `created_by_portal_account_id`, `assigned_to`, `ticket_number`, `subject`, `category`, `queue`, `priority`, `status`, `message`, `first_response_at`, `resolved_at`, `closed_at`, `client_last_read_at`, `staff_last_read_at`

## Route Utama

### Ticket (Admin/Staff)

| Method | URI | Controller | Permission |
|--------|-----|------------|------------|
| GET | `/tickets` | `TicketController@index` | `tickets.view` |
| POST | `/tickets` | `TicketController@store` | `tickets.create` |
| GET | `/tickets/{ticket}` | `TicketController@show` | `tickets.view` |
| PUT/PATCH | `/tickets/{ticket}` | `TicketController@update` | `tickets.update` |
| POST | `/tickets/bulk-update` | `TicketController@bulkUpdate` | `tickets.update` |
| POST | `/tickets/{ticket}/reply` | `TicketController@reply` | `tickets.update` |

### Canned Responses

| Method | URI | Controller |
|--------|-----|------------|
| GET | `/ticket-canned-responses` | `TicketCannedResponseController@index` |
| POST | `/ticket-canned-responses` | `TicketCannedResponseController@store` |
| PUT | `/ticket-canned-responses/{response}` | `TicketCannedResponseController@update` |
| DELETE | `/ticket-canned-responses/{response}` | `TicketCannedResponseController@destroy` |

### Client Portal API

| Method | URI | Keterangan |
|--------|-----|------------|
| GET | `/api/client-portal/tickets` | Daftar tiket client |
| POST | `/api/client-portal/tickets` | Client buat tiket baru |
| GET | `/api/client-portal/tickets/{ticket}` | Detail tiket |
| POST | `/api/client-portal/tickets/{ticket}/reopen` | Client reopen tiket |
| POST | `/api/client-portal/tickets/{ticket}/replies` | Client kirim balasan |

## Permission

| Permission | Deskripsi |
|------------|-----------|
| `tickets.view` | Melihat daftar dan detail tiket |
| `tickets.create` | Membuat tiket baru |
| `tickets.update` | Mengubah status/priority/queue, reply, bulk update |
| `tickets.delete` | Menghapus tiket |
| `tickets.assign` | Assign tiket ke staff |
| `tickets.close` | Menutup tiket |

### Default Role Mapping

| Role | view | create | update | delete | assign | close |
|------|------|--------|--------|--------|--------|-------|
| Owner | v | v | v | v | v | v |
| Admin | v | v | v | v | v | v |
| Employee | v | v | v | - | - | - |
| Billing | v | v | v | - | - | - |
| NOC | v | - | v | - | v | v |
| CS | v | v | v | - | - | - |
| Sales | v | v | - | - | - | - |
| Finance | - | - | - | - | - | - |
| Client | v | v | - | - | - | - |

## Alur Bisnis

### Pembuatan Tiket (Staff)

1. Staff memilih client dan opsional subscription.
2. Mengisi subject, category, queue, priority, dan pesan.
3. Bisa lampirkan file (max 5MB, format: jpg/png/pdf/doc/xls/zip/txt).
4. Sistem generate `ticket_number` format: `TCK-{YYMMDD}-{0001}`.
5. Balasan pertama otomatis dibuat dari pesan tiket.
6. Notifikasi dikirim ke client portal.
7. Queue otomatis di-set berdasarkan category jika tidak dipilih manual.

### Pembuatan Tiket (Client Portal)

1. Client login ke portal, buat tiket dengan subject, category, priority, message.
2. Opsional: lampirkan ke subscription tertentu.
3. Notifikasi dibuat di portal.

### Siklus Tiket

```
open → in_progress → waiting_client → resolved → closed
                   ↗                 ↗
           (client reply)     (reopen)
```

| Status | Deskripsi |
|--------|-----------|
| `open` | Baru dibuat, belum ditangani |
| `in_progress` | Sedang dikerjakan staff |
| `waiting_client` | Menunggu respons client (auto-set saat staff reply) |
| `resolved` | Sudah diselesaikan |
| `closed` | Ditutup final |

### Balasan dan Internal Notes

- **Reply**: Balasan yang terlihat oleh client. Auto-set status ke `waiting_client`.
- **Internal note**: Catatan internal yang hanya terlihat staff (`is_internal = true`). Tidak mengubah status.
- Bubble chat UI: balasan ditampilkan dalam format percakapan.
- Unread tracking: `staff_last_read_at` dan `client_last_read_at` untuk indikator pesan belum dibaca.

### Bulk Update

Staff bisa update banyak tiket sekaligus: status, queue, priority, assigned_to. Setiap perubahan dicatat di activity log dan notifikasi dikirim.

### Canned Responses

- Template balasan cepat dengan placeholder yang di-render otomatis.
- Placeholder didefinisikan di `config/tickets.php`: `{client_name}`, `{ticket_number}`, `{subscription_code}`, dll.
- Bisa dikategorikan per category tiket.
- Sortable via `sort_order`.

### Queue System

Didefinisikan di `config/tickets.php`:

| Queue Key | Label |
|-----------|-------|
| `noc` | NOC |
| `technical` | Technical Support |
| `billing` | Billing |
| `provisioning` | Provisioning |
| `general` | General Support |

Auto-mapping category → queue:
- `connectivity` → `noc`
- `technical` → `technical`
- `billing` → `billing`
- `general` → `general`

### SLA Tracking

| Field | Diisi Saat |
|-------|------------|
| `first_response_at` | Status pertama kali berubah dari `open` ke status lain |
| `resolved_at` | Status berubah ke `resolved` |
| `closed_at` | Status berubah ke `closed` |

### Notifikasi Client Portal

Notifikasi otomatis dibuat di `client_portal_notifications` saat:
- Tiket dibuat oleh staff → `ticket_created`
- Status tiket berubah → `ticket_status`
- Staff membalas tiket (non-internal) → `ticket_reply`

## Integrasi Modul Lain

| Modul | Relasi |
|-------|--------|
| Client | Setiap tiket milik satu client |
| Subscription | Tiket bisa dikaitkan ke langganan tertentu |
| Client Portal | Client bisa buat/reply/reopen tiket via portal API |
| User | Staff di-assign dan reply sebagai author |
| Notifikasi Portal | Auto-notify client saat ada update tiket |

## Seeder / Migration Terkait

| File | Keterangan |
|------|------------|
| `create_tickets_table` | Tabel utama tiket |
| `create_ticket_replies_table` | Tabel balasan |
| `create_ticket_reply_attachments_table` | Tabel attachment |
| `create_ticket_activities_table` | Activity log khusus tiket |
| `create_ticket_canned_responses_table` | Template balasan |
| `config/tickets.php` | Konfigurasi queue, placeholder, category mapping |

## Known Issues / Catatan

- `tickets.assign` dan `tickets.close` permission ada di seeder tapi controller menggunakan `tickets.update` untuk semua perubahan termasuk assign dan close. Pertimbangkan untuk enforce permission lebih granular.
- `tickets.delete` permission ada di seeder tapi `TicketController` tidak expose method `destroy`. Tiket tidak bisa dihapus dari UI.
- Finance role tidak mendapat permission tiket sama sekali. Pertimbangkan apakah perlu `tickets.view` untuk tiket kategori billing.
- Attachment disimpan di disk `public` (`storage/ticket-attachments/`). Pastikan `php artisan storage:link` sudah dijalankan.
- Activity log tiket menggunakan tabel khusus `ticket_activities` (bukan Spatie activity log), tapi model `Ticket` dan `TicketReply` juga menggunakan trait `LogsModelActivity` (Spatie). Ada dua sumber activity log.
- Canned response placeholder di-render saat halaman show tiket di-load, bukan saat disimpan.
