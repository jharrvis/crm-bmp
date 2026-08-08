# Mail Hosting

## Tujuan

Mengelola layanan *mail hosting* untuk langganan pelanggan ISP. Modul ini terintegrasi dengan server **Zimbra** (SOAP Admin API). Operasi ke server mail dijalankan melalui queue agar request web tidak bergantung pada waktu respons SOAP.

## Entitas Terkait

| Model | File | Keterangan |
|-------|------|------------|
| `Subscription` | `app/Models/Subscription.php` | Langganan induk (paket tipe `mail`) |
| `SubscriptionMailHosting` | `app/Models/SubscriptionMailHosting.php` | Detail layanan mail hosting per subscription |
| `Mailbox` | `app/Models/Mailbox.php` | Akun mail dalam hosting |
| `HostingServer` | `app/Models/HostingServer.php` | Server mail (tipe `zimbra`) |
| `Package` | `app/Models/Package.php` | Paket layanan tipe `mail` (max_mailboxes, mailbox_quota_mb, alias_max) |
| `Service` | `app/Models/Service.php` | Jenis layanan `mail` |

### Relasi SubscriptionMailHosting

- `subscription()` → belongsTo `Subscription`
- `mailServer()` → belongsTo `HostingServer`
- `mailboxes()` → hasMany `Mailbox`

### Field SubscriptionMail

| Field | Keterangan |
|-------|------------|
| `subscription_id` | Langganan pemilik |
| `mail_server_id` | Server mail (HostingServer type zimbra) |
| `domain` | Domain mail (mis. `example.com`) |
| `admin_email` | Email admin default (opsional) |
| `max_mailboxes` | Limit jumlah mailbox (dari pak) |
| `mailbox_quota_mb` | Default quota mailbox dalam MB |
| `alias_max` | Maksimum alias per mailbox |
| `mail_server_type` | Jenis engine, default `zimbra`, kandidat `postfix` |
| `status` | `active` \| `suspended` \| `terminated` |

### Field Mailbox

| Field | Keterangan |
|-------|------------|
| `subscription_mail_hosting_id` | Induk mail hosting |
| `email` | Alamat email unik dalam hosting |
| `zimbra_id` | ID akun dari server Zimbra |
| `display_name` | Nama tampilan |
| `password_encrypted` | Password mailbox, dienkripsi dan tidak pernah dikirim pada respons JSON |
| `quota_mb` | Kuota dalam MB |
| `alias_count` | Jumlah alias |
| `is_active` | Status aktif (sync dari server) |

## Route Utama

| Method | URI | Controller | Permission |
|--------|-----|------------|------------|
| GET | `/subscriptions/{subscription}/mailboxes` | `MailboxController@index` | `mailboxes.view` |
| POST | `/subscriptions/{subscription}/mailboxes` | `MailboxController@store` | `mailboxes.create` |
| POST | `/subscriptions/{subscription}/mailboxes/{mailbox}/suspend` | `MailboxController@suspend` | `mailboxes.update` |
| POST | `/subscriptions/{subscription}/mailboxes/{mailbox}/activate` | `MailboxController@activate` | `mailboxes.update` |
| DELETE | `/subscriptions/{subscription}/mailboxes/{mailbox}` | `MailboxController@destroy` | `mailboxes.delete` |

### Subroutes lain

- Form create/edit subscription (paket `mail`) dikelola di `SubscriptionController@store` / `@update`.

## Permission

| Permission | Deskripsi |
|------------|-----------|
| `mailboxes.view` | Melihat daftar mailbox |
| `mailboxes.create` | Membuat mailbox baru |
| `mailboxes.update` | Mengantrekan perubahan status mail (suspend/activate) |
| `mailboxes.delete` | Mengantrekan penghapusan mailbox |

### Default Role Mapping

`PermissionSeeder` membuat permission baru tanpa mencabut permission yang sudah dikonfigurasi pada role di production. Owner dan Admin memperoleh permission mailbox melalui aturan default; role lain harus dikonfigurasi melalui Manajemen Role sesuai kebutuhan operasional.

## Alur Bisnis

### Pembuatan Mailbox

1. Staff membuka tab **Mail Hosting** pada detail subscription.
2. `MailboxController@store` memvalidasi email dan domain wajib sesuai.
3. Cek limit dan quota yang tersimpan pada layanan, bukan nilai paket yang mungkin sudah berubah.
4. Simpan mailbox sebagai `pending`, lalu antrekan provisioning setelah transaction database selesai.
5. Worker memanggil `ZimbraService.createAccount(email, pass, attrs)` dan menandai record `ready` atau `failed`.
6. Jika job gagal, pesan aman ditampilkan di daftar mailbox dan job dapat dicoba ulang dari queue.

### Suspend / Activate

- Perubahan status diantrekan ke worker, kemudian `ZimbraService::suspend(email)` menggunakan `zimbraAccountStatus=maintenance` atau `activate(email)` menggunakan status `active`.
- Suspend/terminate langganan akan mengantrekan suspend semua mailbox aktif. Aktivasi langganan hanya mengaktifkan kembali mailbox yang sebelumnya disuspend oleh status langganan.

### Hapus

- Mailbox diberi status `deleting`; worker memanggil `ZimbraService::deleteAccount(email)` dan baru menghapus data lokal setelah operasi remote berhasil.

## Integrasi Modul Lain

| Modul | Relasi |
|-------|--------|
| Subscription | Induk data langganan |
| Package / Service | Paket mail membawa kuota |
| HostingServer | Server Zimbra (tipe `zimbra`) |
| Infrastructure | Memerlukan jaringan/domainflow yang sudah diregistrasi |

## Adapter Mail Server

`app/Services/MailServerAdapter.php` mendefinisikan interface. `MailServerResolver` memilih adapter berdasarkan tipe server:

- `ensureDomain`
- `createDomain`
- `createAccount`
- `setPassword`
- `suspend`
- `activate`
- `deleteAccount`
- `listAccounts`

`ZimbraService` (SOAP) adalah implementasi pertama. Engine `postfix` dapat ditambahkan dengan mengimplementasi interface yang sama.

Server mail tetap memakai entitas `HostingServer` yang sama dengan web hosting agar credential, permission, audit log, dan koneksi infrastruktur tidak terduplikasi. Tampilan Infrastruktur memisahkan daftar **Server Web Hosting** dan **Server Mail Hosting** berdasarkan tipe.

`HostingServer` untuk mail perlu diisi:

- `type=zimbra`
- `host` dan `port` (default 7071)
- `api_endpoint` (default `/service/admin/soap`)
- `username` atau `api_key` = admin SOAP
- `secret_key` = password admin Zimbra

## Seeder / Migration Terkait

| File | Keterangan |
|------|------------|
| `2026_08_08_000001_add_mail_fields_to_packages_table` | max_mailboxes, mailbox_quota_mb, alias_max |
| `2026_08_08_000002_add_api_endpoint_to_hosting_servers_table` | api_endpoint kolom pada server |
| `2026_08_08_000003_create_subscription_mail_hostings_table` | Tabel induk mail hosting |
| `2026_08_08_000004_create_mailboxes_table` | Tabel mailbox |

Seeder: `PermissionSeeder` menambah modul `mailboxes`.

## Postfix (Engine) Kandidat

- Interface `MailServerAdapter` yang dipakai Zimbra sudah dirancang agar `Postfix` (mis. via admin shell atau API) dapat diimplementasikan nanti menggunakan interface yang sama.
- Pada saat ini kredensial per server dikelola di `HostingServer`.

## Known Issues / Catatan

- Zimbra: `zimbra` account status `maintenance` dipakai untuk suspend.
- Auth token dik-cache 55 menit per server dan dibersihkan saat konfigurasi server diperbarui.
- Credential tidak disertakan pada respons JSON atau activity log.
- Domain/server tidak dapat diganti setelah mailbox dibuat; gunakan prosedur migrasi mail hosting.
- Alias belum diimplementasikan. `alias_max` hanya dicatat sebagai batas paket dan tidak boleh ditampilkan sebagai fitur yang sudah tersedia.
- Gunakan versi Zimbra yang masih menerima security update; Zimbra 8.8.15 sudah melewati masa technical guidance.
