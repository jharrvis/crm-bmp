# Mail Hosting

## Tujuan

Mengelola layanan *mail hosting* untuk langganan pelanggan ISP. Modul ini terintegrasi dengan server **Zimbra** (SOAP Admin API) dalam mode **satu arah/read-only**: CRM membaca metadata Zimbra dan menyimpan salinan lokal untuk kebutuhan operasional, tanpa membuat atau mengubah akun, domain, maupun isi mailbox di Zimbra.

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
| `used_quota_mb` | Pemakaian ruang terakhir dari Zimbra dalam MB; `null` bila atribut belum tersedia |
| `alias_count` | Jumlah alias |
| `is_active` | Status aktif (sync dari server) |
| `managed_by_crm` | Penanda asal record. Pada integrasi Zimbra read-only, akun hasil sync selalu `false` dan tidak dapat diubah dari CRM |
| `remote_status` | Status terakhir dari Zimbra, mis. `active`, `maintenance`, atau `locked` |

## Route Utama

| Method | URI | Controller | Permission |
|--------|-----|------------|------------|
| GET | `/subscriptions/{subscription}/mailboxes` | `MailboxController@index` | `mailboxes.view` |
| POST | `/subscriptions/{subscription}/mailboxes` | `MailboxController@store` | `mailboxes.create` (ditolak untuk Zimbra read-only) |
| POST | `/subscriptions/{subscription}/mailboxes/sync` | `MailboxController@sync` | `mailboxes.sync` |
| POST | `/subscriptions/{subscription}/mailboxes/{mailbox}/suspend` | `MailboxController@suspend` | `mailboxes.update` |
| POST | `/subscriptions/{subscription}/mailboxes/{mailbox}/activate` | `MailboxController@activate` | `mailboxes.update` |
| DELETE | `/subscriptions/{subscription}/mailboxes/{mailbox}` | `MailboxController@destroy` | `mailboxes.delete` |

### Subroutes lain

- Form create/edit subscription (paket `mail`) dikelola di `SubscriptionController@store` / `@update`.

## Permission

| Permission | Deskripsi |
|------------|-----------|
| `mailboxes.view` | Melihat daftar mailbox |
| `mailboxes.create` | Menambah mailbox untuk adapter write-enabled di masa depan; tidak tersedia untuk Zimbra |
| `mailboxes.update` | Mengubah status mailbox untuk adapter write-enabled di masa depan; tidak tersedia untuk Zimbra |
| `mailboxes.delete` | Menghapus mailbox untuk adapter write-enabled di masa depan; tidak tersedia untuk Zimbra |
| `mailboxes.sync` | Mengantrekan sinkronisasi read-only mailbox existing dari Zimbra |

### Default Role Mapping

`PermissionSeeder` membuat permission baru tanpa mencabut permission yang sudah dikonfigurasi pada role di production. Owner dan Admin memperoleh permission mailbox melalui aturan default; role lain harus dikonfigurasi melalui Manajemen Role sesuai kebutuhan operasional.

## Alur Bisnis

### Domain Layanan dan Mailbox

1. Saat membuat layanan mail hosting, admin memilih domain yang sudah tercatat pada langganan domain client atau mengisi domain manual bila belum ada langganan domain.
2. Domain dan server mail disimpan pada `subscription_mail_hostings`; domain menjadi batas tunggal untuk sinkronisasi mailbox.
3. Daftar lengkap mailbox hanya tersedia pada **Kelola Mailbox**, bukan lagi diduplikasi pada detail layanan.
4. Pada Zimbra, tombol tambah mailbox tidak ditampilkan. Pembuatan akun dilakukan dari panel Zimbra, kemudian ditarik ke CRM melalui sinkronisasi.

### Sinkronisasi Mailbox Existing dari Zimbra

1. Staff dengan permission `mailboxes.sync` menekan **Sinkronkan dari Zimbra** pada daftar mailbox.
2. Job membaca akun untuk domain layanan menggunakan filter Zimbra `(mail=*@domain)` serta metadata `displayName`, `zimbraMailQuota`, dan `zimbraAccountStatus`. Operasi ini tidak membuat, mengubah, menonaktifkan, maupun menghapus akun di Zimbra.
3. Akun yang belum ada di tabel lokal `mailboxes` diimpor dengan `managed_by_crm=false`, status `ready`, dan tanpa password.
4. Untuk akun yang sudah terkait pada layanan yang sama, CRM memperbarui metadata lokal status, quota, dan display name dari Zimbra. Jika email sudah terkait ke layanan mail hosting lain, akun dilewati dan relasinya tidak diubah.
5. Mailbox hasil impor tampil sebagai **Read-only dari Zimbra**. Aksi suspend, activate, dan hapus tidak tersedia dari CRM untuk mencegah perubahan tidak sengaja pada akun legacy.

### Pembaruan Saat Halaman Dibuka

- Saat halaman detail subscription mail hosting atau halaman **Kelola Mailbox** dibuka oleh user dengan `mailboxes.view`, CRM menjalankan pull metadata Zimbra secara langsung. Halaman Kelola Mailbox juga menampilkan pemakaian ruang aktual setiap akun (`zimbraMailUsedQuota`) terhadap quota mailbox bila atribut tersedia.
- Jika Zimbra gagal dihubungi, halaman tetap menampilkan data lokal terakhir beserta peringatan aman. Kegagalan tidak menghapus atau mengubah status mailbox lokal.
- Daftar mailbox menggunakan pagination dan pencarian alamat email live dari database lokal setelah proses pull selesai. Pencarian live tidak memanggil Zimbra berulang kali.

### Batas Domain dan Penghapusan Layanan

- Domain adalah field wajib pada setiap layanan mail hosting. Sinkronisasi hanya menerima email dengan suffix `@domain` yang tersimpan pada layanan tersebut; mailbox dari domain lain tidak akan ditampilkan atau ditautkan.
- Aksi hapus pada layanan mail hosting tidak melakukan hard delete. CRM mengubah status layanan menjadi `terminated` dan mempertahankan record mail hosting serta mailbox lokal sebagai arsip.
- Penghapusan layanan tidak memanggil `DeleteAccount`, `DeleteDomain`, atau API Zimbra lain. Akun, domain, dan isi mailbox di Zimbra tetap utuh.
- Job provisioning, perubahan status, atau penghapusan mailbox yang masih mengantre diblokir untuk Zimbra, sehingga tidak ada operasi tulis lanjutan dari CRM.

### Batas Aksi Zimbra

- CRM tidak menyediakan tambah, suspend, aktifkan, hapus, reset password, atau provisioning domain untuk Zimbra.
- Username dan password admin yang tampil pada detail layanan dibaca dari konfigurasi `HostingServer`, bukan dari data langganan. Password hanya dapat disalin oleh user dengan permission `servers.manage`; setiap akses dicatat di Activity Log tanpa menyimpan nilai password pada log.
- Informasi server yang ditampilkan adalah konfigurasi yang tersedia secara lokal (engine, host/port, lokasi). Versi Zimbra dan OS tidak ditampilkan sampai endpoint read-only yang stabil tersedia dan teruji.

## Integrasi Modul Lain

| Modul | Relasi |
|-------|--------|
| Subscription | Induk data langganan |
| Package / Service | Paket mail membawa kuota |
| HostingServer | Server Zimbra (tipe `zimbra`) |
| Infrastructure | Memerlukan jaringan/domainflow yang sudah diregistrasi |

## Adapter Mail Server

`app/Services/MailServerAdapter.php` mendefinisikan interface. `MailServerResolver` memilih adapter berdasarkan tipe server:

- `listAccounts`

`ZimbraService` (SOAP) adalah implementasi pertama. Engine `postfix` dapat ditambahkan dengan mengimplementasi interface yang sama.

Server mail tetap memakai entitas `HostingServer` yang sama dengan web hosting agar credential, permission, audit log, dan koneksi infrastruktur tidak terduplikasi. Tampilan Infrastruktur memisahkan daftar **Server Web Hosting** dan **Server Mail Hosting** berdasarkan tipe.

### Detail Server Zimbra

Halaman detail server mail membaca data berikut secara *read-only* dan menyimpannya pada cache singkat (5 menit):

- `GetServerRequest`: ID/hostname server, service yang diaktifkan, serta port service yang dikonfigurasi.
- `GetVersionInfoRequest`: versi Zimbra jika server mengizinkan informasi versi melalui API.
- `GetServiceStatusRequest`: status `running/stopped` service pada hostname server.

Data OS, kapasitas disk fisik, CPU, RAM, dan beban server tidak diambil dari Admin SOAP API pada tahap ini karena bukan kontrak data yang konsisten untuk semua instalasi Zimbra. Gunakan Zabbix atau monitoring agent untuk metrik infrastruktur tersebut.

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
| `2026_08_11_000001_add_managed_by_crm_to_mailboxes_table` | Penanda mailbox yang dibuat CRM atau diimpor read-only dari Zimbra |
| `2026_08_11_000002_add_remote_sync_metadata_to_mailboxes_table` | Status remote dan waktu/error sinkronisasi mailbox Zimbra |

Seeder: `PermissionSeeder` menambah modul `mailboxes`, termasuk permission `mailboxes.sync`. Seeder menambahkan permission tanpa mereset permission role custom yang sudah ada.

## Postfix (Engine) Kandidat

- Interface `MailServerAdapter` yang dipakai Zimbra sudah dirancang agar `Postfix` (mis. via admin shell atau API) dapat diimplementasikan nanti menggunakan interface yang sama.
- Pada saat ini kredensial per server dikelola di `HostingServer`.

## Known Issues / Catatan

- Auth token dik-cache 55 menit per server dan dibersihkan saat konfigurasi server diperbarui.
- Credential tidak disertakan pada respons JSON atau activity log.
- Sinkronisasi mailbox existing bersifat import metadata satu arah. Status, quota, dan display name dibaca dari Zimbra; alias dan deteksi akun remote yang sudah hilang belum diimplementasikan.
- Domain/server tidak dapat diganti setelah mailbox dibuat; gunakan prosedur migrasi mail hosting.
- Alias belum diimplementasikan. `alias_max` hanya dicatat sebagai batas paket dan tidak boleh ditampilkan sebagai fitur yang sudah tersedia.
- Gunakan versi Zimbra yang masih menerima security update; Zimbra 8.8.15 sudah melewati masa technical guidance.
