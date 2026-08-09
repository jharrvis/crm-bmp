# Web Hosting (Manage Server HestiaCP)

## Tujuan

Menyediakan operation console untuk server web hosting **HestiaCP**. Operator dapat
melihat ringkasan snapshot server, daftar user live, menautkan user Hestia existing ke
subscription, dan mengantrekan operasi lifecycle (provision, suspend/activate, reset
password, delete) melalui queue.

Modul ini hanya berlaku untuk entitas `HostingServer` dengan `type=hestiacp` dan
`is_active=true`. Server Zimbra (mail) tidak termasuk.

## Entitas Terkait

| Model | File | Keterangan |
|-------|------|------------|
| `HostingServer` | `app/Models/HostingServer.php` | Server HestiaCP (`type=hestiacp`) |
| `HostingServerSnapshot` | `app/Models/HostingServerSnapshot.php` | Ringkasan snapshot server |
| `SubscriptionHosting` | `app/Models/SubscriptionHosting.php` | Detail hosting per subscription |
| `Subscription` | `app/Models/Subscription.php` | Langganan induk |
| `Package` | `app/Models/Package.php` | Paket layanan (membawa `hestia_package`) |

### Relationship SubscriptionHosting

- `subscription()` → belongsTo `Subscription`
- `hostingServer()` → belongsTo `HostingServer`

### Field SubscriptionHosting

| Field | Keterangan |
|-------|------------|
| `subscription_id` | Langganan pemilik (unique) |
| `hosting_server_id` | Server HestiaCP |
| `domain` | Domain web |
| `username` | Username Hestia (unique per server) |
| `password_encrypted` | Password akun, encrypted dan tidak pernah dikirim keluar |
| `disk_quota_gb`, `email_accounts`, `databases` | Kuota teknis |
| `ssl_expiry` | Tanggal kedaluwarsa SSL |
| `provisioning_status` | `pending` \| `provisioning` \| `ready` \| `failed` \| `deleting` \| `delete_failed` |
| `provisioning_error` | Pesan aman untuk operator (nullable) |
| `provisioned_at` | Waktu provisioning selesai |
| `remote_user_created_at` | Bukti waktu user berhasil dibuat oleh workflow CRM |
| `delete_requested_at` | Waktu permintaan delete yang sudah dikonfirmasi Owner |
| `managed_by_crm` | `true` = dibuat CRM, `false` = hanya ditautkan (read-only lifecycle) |
| `suspended_by_subscription` | Penanda suspend oleh status langganan |
| `hestia_package` | Nama paket Hestia tujuan (wajib untuk akun baru yang dikelola CRM; kosong untuk akun existing yang hanya ditautkan) |

## Route Utama

| Method | URI | Controller | Permission |
|--------|-----|------------|------------|
| GET | `/servers/{server}/manage` | `ServerManageController@show` | `servers.manage` |
| POST | `/servers/{server}/test-connection` | `ServerManageController@testConnection` | `servers.connect` |
| GET | `/servers/{server}/users` | `ServerManageController@users` | `servers.manage` |
| POST | `/servers/{server}/users/link` | `ServerManageController@link` | `servers.provision` |
| POST | `/servers/{server}/users/suspend` | `ServerManageController@suspend` | `servers.suspend` |
| POST | `/servers/{server}/users/activate` | `ServerManageController@activate` | `servers.suspend` |
| POST | `/servers/{server}/users/password` | `ServerManageController@resetPassword` | `servers.reset_password` |
| DELETE | `/servers/{server}/users` | `ServerManageController@destroy` | `servers.delete_user` |
| POST | `/servers/{server}/refresh` | `ServerManageController@refresh` | `servers.manage` |

Username dikirim pada request body, bukan sebagai parameter route, dan divalidasi
`^[a-zA-Z0-9_]{1,32}$`. Akun baru yang dibuat CRM dibatasi `^[a-z][a-z0-9_]{0,31}$`
(tanpa tanda `-` sampai diverifikasi terhadap versi Hestia production).

## Permission

| Permission | Deskripsi |
|------------|-----------|
| `servers.connect` | Test koneksi dan melihat data live server |
| `servers.manage` | Melihat halaman manage dan daftar user |
| `servers.provision` | Membuat/link user/domain ke subscription |
| `servers.suspend` | Suspend/aktifkan akun |
| `servers.reset_password` | Reset password akun |
| `servers.delete_user` | Hapus user remote |

Default role (dari `PermissionSeeder`, additive via `givePermissionTo`):

| Role | Permission default |
|------|--------------------|
| Owner | semua |
| Admin | semua kecuali `servers.delete_user` |
| NOC | `servers.connect`, `servers.manage`, `servers.suspend` |
| Employee/CS/Sales/Billing/Finance | tidak ada secara default |

`servers.delete_user` tidak diberikan ke NOC. Seeder tidak memakai `syncPermissions`,
permission custom yang dibuat admin di production tetap utuh.

## Alur Bisnis

### Provisioning langganan hosting (dari form subscription)

1. Paket dipilih berjenis `hosting`.
2. Validasi server `hestiacp` aktif, username memenuhi pola, domain FQDN, password
   minimal 8 karakter.
3. `SubscriptionController@store` membuat `Subscription` + `SubscriptionHosting`
   status `pending` dalam satu transaction, lalu commit.
4. `ProvisionHostingAccountJob` di-dispatch `afterCommit()`.
5. Worker memverifikasi email kontak utama, package dan kapasitas server sebelum membuat user dan web domain, lalu set `provisioning_status=ready`.
6. Jika username sudah ada tanpa penanda `remote_user_created_at`, job gagal aman dan tidak mengubah akun/domain existing.

### Menautkan akun existing dari form layanan

Form tambah dan edit langganan hosting menyediakan dua mode akun:

1. **Buat akun baru**: membutuhkan mapping `hestia_package` dan password, lalu menjalankan provisioning melalui queue.
2. **Tautkan user existing**: membutuhkan username dan domain yang telah ada. CRM memverifikasi keduanya melalui `v-list-user` dan `v-list-web-domains`, kemudian menyimpan relasi dengan `managed_by_crm=false` dan status `ready` tanpa memanggil API perubahan apa pun.

Pada mode tautkan, operator memilih server terlebih dahulu, lalu mencari username dari
daftar user HestiaCP dan memilih domain yang dimiliki username tersebut. Data picker
hanya mengirim username/domain, memakai cache sukses selama dua menit, dan tetap
divalidasi ulang oleh server saat disimpan.

Akun existing yang ditautkan bersifat read-only untuk lifecycle CRM. Server, username,
domain, dan password tidak dapat diubah dari form langganan agar tidak ada perubahan
tidak sengaja pada data hosting yang sudah berjalan.

### Test Koneksi dan Refresh

- `testConnection` memanggil `WebHostResolver->resolve($server)->testConnection()`.
- `refresh` mengantrekan `RefreshHestiaServerSnapshotJob` yang menyimpan snapshot baru
  dengan `status synced/failed`, satu snapshot aktif (`is_active=true`) per server.

### Daftar User (live + cache)

`users` mengambil daftar user dari server dengan `Cache::remember` TTL 120 detik,
kemudian menggabungkan data keterkaitan dari `SubscriptionHosting`. Akun `managed_by_crm=true`
menampilkan aksi lifecycle; akun yang hanya ditautkan (`managed_by_crm=false`) read-only.

### Link User Existing

- Subscription harus berjenis layanan `hosting` dan belum punya `SubscriptionHosting`.
- Username serta domain harus benar-benar ada dan saling terkait di server Hestia.
- Record dibuat `managed_by_crm=false`, status `ready` tanpa memanggil `createUser` atau `createWebDomain`.

### Suspend / Activate / Reset Password

- Semua operasi diantrekan melalui job (`SetHostingAccountStatusJob`,
  `ResetHostingAccountPasswordJob`) setelah transaction commit.
- Password baru dienkripsi lalu dikirim hanya sebagai ID (bukan nilai mentah) ke job.
- Akun linked, legacy, pending, atau gagal provisioning tidak dapat menerima lifecycle action.
- Status invoice/langganan belum mengubah status HestiaCP secara otomatis.

### Hapus

- Hanya Owner dengan permission `servers.delete_user` yang dapat menghapus akun yang terbukti dibuat CRM, via `DeleteHostingAccountJob`.
- Owner wajib mengetik ulang username; validasi ini dilakukan kembali di server-side.
- Record lokal baru dihapus setelah operasi remote sukses; jika gagal berubah menjadi
  `delete_failed`.
- Akun `managed_by_crm=false` bersifat read-only; override Owner ditunda sebagai backlog.
- User `admin` tidak dapat dihapus.

## Integrasi Modul Lain

| Modul | Relasi |
|-------|--------|
| Infrastruktur | Data server pada menu Server Web Hosting |
| Subscription | Langganan pemilik detail hosting |
| Package / Service | Paket hosting (`hestia_package`) |
| Permission | `servers.*` baru |

## Adapter Web Hosting

Interface `app/Services/WebHostingServerAdapter.php`, resolver
`app/Services/WebHostResolver.php` memilih adapter berdasar tipe server:

- `testConnection`
- `listUsers`
- `findUser`
- `listWebDomains`
- `listUserPackages`
- `createUser`
- `createWebDomain`
- `suspendUser`
- `unsuspendUser`
- `changePassword`
- `deleteUser`

`HestiaCPService` adalah implementasi pertama. Kredensial dibangun dari
`HostingServer.api_key` (`AccessKey`) dan `secret_key` (`SecretKey`) dengan format
`ACCESS_KEY:SECRET_KEY`, keduanya dienkripsi di model dan tidak diputar balik ke JSON.

Konfigurasi TLS mengikuti `config/hestiacp.php`: `verify_ssl` default `true` (
`HESTIACP_VERIFY_SSL`). `Http::timeout(30)` dipakai; verifikasi sertifikat tidak
dinonaktifkan global.

### Konfigurasi Access Key HestiaCP

Access Key harus dibuat untuk user `admin` atau akun operasional yang memang memiliki
hak menjalankan command tersebut. Permission minimum untuk fitur CRM saat ini:

- `v-list-users`, `v-list-user`, `v-list-user-packages`, `v-list-web-domains`
- `v-add-user`, `v-add-web-domain`
- `v-suspend-user`, `v-unsuspend-user`, `v-change-user-password`, `v-delete-user`

Aktifkan API Hestia dan whitelist **IP publik server CRM** pada server Hestia.
Tombol Test Koneksi memakai `v-list-users`, sehingga sukses berarti koneksi, key,
permission minimum, dan whitelist dapat digunakan oleh integrasi.

## Seeder / Migration Terkait

| File | Keterangan |
|------|------------|
| `2026_08_08_000006_add_provisioning_to_subscription_hostings_table` | Kolom provisioning, unique index subscription_id & (hosting_server_id, username) |
| `2026_08_08_000007_create_hosting_server_snapshots_table` | Tabel snapshot |
| `2026_08_08_000008_add_hestia_package_to_packages_table` | Kolom `hestia_package` |
| `2026_08_08_000009_add_remote_operation_markers_to_subscription_hostings_table` | Penanda asal akun CRM dan permintaan delete |
| `PermissionSeeder` | Permission `servers.*` baru |

Seeder dipakai `givePermissionTo` dan tidak mencabut permission custom. Sebelum
mengaktifkan unique index, migration mendeteksi duplikat legacy dan menghentikan
deploy dengan pesan jelas.

## Deployment

```bash
php artisan migrate
php artisan db:seed --class=PermissionSeeder
php artisan permission:cache-reset
php artisan optimize:clear
php artisan queue:restart
```

Pastikan queue worker production aktif. Setiap operator harus memastikan:
- Hestia access/secret key hanya punya profile command minimum.
- IP server CRM di-whitelist di HestiaCP.
- Uji test connection, create/link/suspend pada akun non-production terlebih dahulu.

## Known Issues / Catatan

- Username baru tidak menerima tanda `-` sampai diverifikasi terhadap Hestia production.
- Domain/username tidak dapat diubah setelah provisioning `ready`; gunakan migrasi hosting.
- Override delete untuk akun non-CRM dan migrasi otomatis antar server belum ada di fase pertama.
- Mapping paket Hestia wajib diisi pada paket CRM dan diverifikasi di server target.
