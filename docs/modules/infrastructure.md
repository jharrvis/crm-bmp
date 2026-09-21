# Infrastruktur

## Tujuan

Modul Infrastruktur menyimpan data perangkat dan referensi jaringan yang dipakai operasional, termasuk Router MikroTik, server hosting, vendor, Metro Ethernet, dan monitor Zabbix.

## Router

### Route Utama

- `GET /routers` - daftar router
- `GET /routers/{router}` - detail router
- `POST /routers` - membuat router
- `PUT /routers/{router}` - memperbarui router
- `DELETE /routers/{router}` - menghapus router

### Permission

- `routers.view` - membuka daftar dan detail router
- `routers.create` - membuat router
- `routers.update` - memperbarui router
- `routers.delete` - menghapus router
- `routers.connect` - placeholder untuk integrasi koneksi router di masa depan

### Detail Router

Halaman detail Router menampilkan host, port API, cabang, status, peran, deskripsi, username, dan password router. Tombol Edit membuka form Router dengan data perangkat yang sudah terisi. Password disimpan dengan encrypted cast Laravel, dikecualikan dari Activity Log, dan tersedia melalui kontrol tampilkan/sembunyikan atau salin bagi pengguna yang mempunyai `routers.view`.

Peran Router bersifat opsional. Pilihan standar: Core, POP, Distribusi, Akses, Customer Gateway, dan Management. Pilihan Lainnya membuka isian peran custom.

Daftar langganan yang terkait ditentukan dari relasi `subscription_connectivities.router_id`, termasuk pelanggan, kode langganan, paket, alamat IP, dan PPPoE user bila tersedia.

### Integrasi

- Global Search mengarahkan hasil Router ke halaman detail router.
- Subscription Connectivity memilih Router sebagai gateway/langganan terkait.
- Cabang menjadi penanda lokasi atau kepemilikan operasional router.

## Metro Ethernet

### Tujuan

Mencatat layanan Metro Ethernet dari vendor, termasuk CID, IP Address, dan bandwidth koneksi.

### Route dan Permission

- Halaman: `/metro-ethernets`
- Detail: `/metro-ethernets/{metroEthernet}`
- Permission: `metro_ethernets.view`, `metro_ethernets.create`, `metro_ethernets.update`, dan `metro_ethernets.delete`

### Integrasi

- Terhubung ke master Vendor.
- Muncul pada Global Search dengan Quick View dan halaman detail.

## IP Transit

### Tujuan

Mencatat koneksi IP Transit yang disediakan vendor untuk kebutuhan operasional jaringan. Data ini dipisahkan dari Metro Ethernet karena memiliki identitas jaringan dan parameter routing tersendiri.

### Data yang Dicatat

- Vendor
- Nama koneksi
- CID (Circuit ID)
- IP Address
- IP Gateway
- AS Number
- Bandwidth dalam Mbps

### Route dan Permission

- Halaman: `/ip-transits`
- Permission: `ip_transits.view`, `ip_transits.create`, `ip_transits.update`, dan `ip_transits.delete`
- Role default: Owner, Admin, dan NOC

### Integrasi

- Terhubung ke master Vendor.
- Muncul pada Global Search untuk pencarian CID, IP, gateway, AS Number, bandwidth, atau vendor.
- Perubahan data dicatat oleh Activity Log melalui trait `LogsModelActivity`.
- Halaman detail tersedia melalui `/ip-transits/{ipTransit}`.

### Migration Terkait

- `2026_08_07_090000_create_ip_transits_table.php`
- `2026_08_07_100000_add_name_to_ip_transits_table.php`

## Internet Backup

### Tujuan

Mencatat koneksi internet backup dari provider berbeda yang digunakan sebagai cadangan koneksi utama pelanggan. Modul ini bersifat inventory operasional; belum termasuk integrasi billing, monitoring provider, atau failover otomatis.

### Data yang Dicatat

- Vendor (master `vendors` yang sudah ada)
- Subscription (nullable saat status `planned`)
- Nama koneksi
- Circuit ID
- IP Address / CIDR
- Gateway
- Bandwidth dalam Mbps
- Biaya bulanan provider (dicatat saja, tidak otomatis invoice)
- Tanggal aktif
- Alamat / lokasi
- Status: `planned`, `active`, `suspended`, `terminated`
- Catatan
- Soft delete (`deleted_at`)

### Relasi

- Internet Backup belongsTo `Vendor`
- Internet Backup belongsTo `Subscription` (nullable)
- Subscription hasMany Internet Backup

### Aturan Bisnis

- Backup berstatus `active` wajib memiliki `subscription_id`.
- Satu subscription dapat memiliki beberapa backup tanpa aturan primary/secondary.
- Biaya bulanan hanya dicatat sebagai biaya provider internal.
- Tidak menyimpan kredensial provider (username, password, API key).
- Data `terminated` dipertahankan untuk histori.

### Route dan Permission

- Halaman: `/internet-backups`
- Detail: `/internet-backups/{internetBackup}`
- Permission: `internet_backups.view`, `internet_backups.create`, `internet_backups.update`, `internet_backups.delete`
- Role default: Owner, Admin, dan NOC

### Integrasi

- Terhubung ke master Vendor dan Subscription.
- Muncul pada Global Search untuk pencarian nama koneksi, circuit ID, IP, vendor, kode subscription, atau nama client.
- Perubahan data dicatat oleh Activity Log melalui trait `LogsModelActivity`.
- Filter DataTables berdasarkan vendor dan status.

### Migration Terkait

- `2026_09_21_000001_create_internet_backups_table.php`

## Server Web Hosting (HestiaCP)

Detail lengkap ada di `docs/modules/web-hosting.md`. Ringkasan:

- Halaman `GET /servers/{server}/manage` (akses: `servers.manage`) menampilkan snapshot
  ringkasan dan tombol Test Koneksi / Refresh Data.
- Daftar user live di `/servers/{server}/users` memakai cache 120 detik.
- Permission baru: `servers.manage`, `servers.provision`, `servers.suspend`,
  `servers.reset_password`, `servers.delete_user` (selain `servers.connect`).
- Operasi remote berjalan melalui queue (`ProvisionHostingAccountJob`,
  `SetHostingAccountStatusJob`, `ResetHostingAccountPasswordJob`,
  `DeleteHostingAccountJob`, `RefreshHestiaServerSnapshotJob`).
- Hanya server `type=hestiacp` dan `is_active=true` yang dapat dikelola.

## Server Mail Hosting

- Server mail menggunakan tipe `zimbra` untuk integrasi SOAP Admin API.
- Tipe `postfix` tersedia sebagai inventaris pending; belum memiliki adapter, test koneksi, atau pengelolaan akun.

## Catatan Keamanan

- Jangan mencatat password, API key, atau credential perangkat ke Activity Log.
- Jangan mengubah `APP_KEY` tanpa rencana migrasi data terenkripsi karena credential router perlu didekripsi dengan key yang sama.
