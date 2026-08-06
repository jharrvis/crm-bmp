# Clients / Pelanggan

## Tujuan

Mengelola data pelanggan ISP per cabang. Setiap client memiliki kode unik yang di-generate berdasarkan cabang dan tahun registrasi, serta dapat memiliki banyak kontak, langganan, tagihan, tiket, dan akun portal.

## Entitas Terkait

| Model | File | Keterangan |
|-------|------|------------|
| `Client` | `app/Models/Client.php` | Entitas utama pelanggan |
| `ClientContact` | `app/Models/ClientContact.php` | Kontak PIC pelanggan (multi, satu primary) |
| `ClientPortalAccount` | `app/Models/ClientPortalAccount.php` | Akun login client portal |

### Relasi Client

- `branch()` → belongsTo `Branch`
- `user()` → belongsTo `User` (pembuat)
- `contacts()` → hasMany `ClientContact`
- `primaryContact()` → hasOne `ClientContact` (where `is_primary = true`)
- `subscriptions()` → hasMany `Subscription`
- `invoices()` → hasMany `Invoice`
- `tickets()` → hasMany `Ticket`
- `portalAccount()` → hasOne `ClientPortalAccount`

### Field Client

`branch_id`, `user_id`, `client_code`, `registered_at`, `name`, `type`, `custom_type`, `identity_number`, `address`, `city`, `postal_code`, `latitude`, `longitude`, `status`, `notes`

### Field ClientContact

`client_id`, `name`, `position`, `email`, `phone`, `whatsapp`, `is_primary`

## Route Utama

### Client CRUD

| Method | URI | Controller | Permission |
|--------|-----|------------|------------|
| GET | `/clients` | `ClientController@index` | `clients.view` |
| POST | `/clients` | `ClientController@store` | `clients.create` |
| GET | `/clients/{client}` | `ClientController@show` | `clients.view` |
| PUT/PATCH | `/clients/{client}` | `ClientController@update` | `clients.update` |
| DELETE | `/clients/{client}` | `ClientController@destroy` | `clients.delete` |

### Client Contacts (nested)

| Method | URI | Controller | Permission |
|--------|-----|------------|------------|
| POST | `/clients/{client}/contacts` | `ClientContactController@store` | `clients.update` |
| PUT | `/clients/{client}/contacts/{contact}` | `ClientContactController@update` | `clients.update` |
| DELETE | `/clients/{client}/contacts/{contact}` | `ClientContactController@destroy` | `clients.update` |

### Portal Account Management (Owner/Admin only)

| Method | URI | Controller |
|--------|-----|------------|
| POST | `/clients/{client}/portal-account` | `ClientPortalAccountController@store` |
| PUT | `/clients/{client}/portal-account` | `ClientPortalAccountController@update` |
| POST | `/clients/{client}/portal-account/revoke-sessions` | `ClientPortalAccountController@revokeSessions` |
| POST | `/clients/{client}/portal-account/generate-otp` | `ClientPortalAccountController@generateOtp` |

Portal account management dibatasi via route-level `role:Owner|Admin` middleware, bukan permission-based.

## Permission

| Permission | Deskripsi |
|------------|-----------|
| `clients.view` | Melihat daftar dan detail client |
| `clients.create` | Membuat client baru |
| `clients.update` | Mengubah data client dan mengelola kontak |
| `clients.delete` | Menghapus client |

### Default Role Mapping

| Role | view | create | update | delete |
|------|------|--------|--------|--------|
| Owner | v | v | v | v |
| Admin | v | v | v | v |
| Employee | v | v | v | - |
| Billing | v | - | - | - |
| NOC | v | - | - | - |
| CS | v | v | v | - |
| Sales | v | v | v | - |
| Finance | v | - | - | - |

## Alur Bisnis

1. **Registrasi client**: Staff mengisi form (nama, tipe, alamat, cabang, kontak PIC). Tipe standar mencakup Perorangan, Bisnis, Pemerintah, Pendidikan, Nirlaba, Keagamaan, Komunitas, dan Properti Bersama. Pilihan Lainnya mewajibkan kategori custom.
2. **Generate client_code**: Otomatis format `{branch_id}{YY}{NNN}` (contoh: `126001`). Collision-safe dengan do-while loop.
3. **Kontak PIC**: Kontak pertama otomatis jadi primary. Primary contact tidak bisa dihapus. Satu client bisa punya banyak kontak.
4. **Detail client**: Menampilkan tab subscriptions, invoices, tickets, portal account.
5. **Portal account**: Owner/Admin bisa membuat akun portal untuk client, generate OTP manual, revoke semua sesi aktif.
6. **Hapus client**: Cascade delete via migration (semua data terkait ikut terhapus).

### UI

- Index: DataTables server-side dengan filter cabang dan status. UI modal-based untuk create/edit.
- Show: Detail client dengan tab informasi, kontak, langganan, tagihan, tiket, portal.

## Integrasi Modul Lain

| Modul | Relasi |
|-------|--------|
| Branch | Client wajib punya cabang |
| Subscription | Client bisa punya banyak langganan |
| Invoice | Tagihan dibuatkan per client |
| Ticket | Tiket support terkait client |
| Client Portal | Akun portal OTP-based, dikelola dari halaman client |

## Seeder / Migration Terkait

| File | Keterangan |
|------|------------|
| `create_clients_table` | Tabel utama client |
| `create_client_contacts_table` | Tabel kontak PIC |
| `SalatigaClientSeeder` | Import client Salatiga |
| `SemarangClientSeeder` | Import client Semarang |
| `KudusInternetClientSeeder` | Import client Kudus |
| `BackfillClientCodeToBranchYearFormatSeeder` | Migrasi format client_code lama ke baru |

## Known Issues / Catatan

- Portal account management menggunakan route-level role check (`Owner|Admin`), bukan permission-based. Pertimbangkan migrasi ke permission `portal_accounts.manage` untuk konsistensi.
- Tidak ada soft delete pada client. Penghapusan bersifat permanen (cascade).
- Activity log aktif pada model `Client` (entity name: `pelanggan`) dan `ClientContact` (entity name: `kontak pelanggan`).
