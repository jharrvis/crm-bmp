# Plan CRUD Internet Backup

## Ringkasan

Menambahkan modul **Internet Backup** di dalam grup menu **Infrastruktur** untuk mencatat koneksi backup pelanggan yang disediakan oleh provider berbeda dari koneksi utama.

MVP berbentuk inventory operasional yang dapat ditautkan ke satu subscription pelanggan. Belum termasuk pembuatan tipe layanan baru, invoice otomatis, atau penyimpanan kredensial provider.

## Perubahan Utama

- Tambahkan entitas `InternetBackup` dan tabel `internet_backups`.
- Field utama:
  - `vendor_id`
  - `subscription_id` nullable saat status masih `planned`
  - nama/keterangan koneksi
  - circuit ID
  - IP address/CIDR
  - gateway
  - bandwidth Mbps
  - biaya bulanan provider
  - tanggal aktif
  - alamat/lokasi
  - status: `planned`, `active`, `suspended`, `terminated`
  - catatan
  - `deleted_at`, `created_at`, `updated_at`
- Relasi:
  - Internet Backup belongsTo `Vendor`
  - Internet Backup belongsTo `Subscription`
  - Subscription hasMany Internet Backup
  - Client ditampilkan melalui relasi Subscription
- Satu record backup maksimal ditautkan ke satu subscription. Satu subscription dapat memiliki beberapa backup bila diperlukan, tanpa aturan primary/secondary pada MVP.
- Biaya bulanan hanya dicatat sebagai biaya provider internal; tidak otomatis masuk invoice pelanggan.
- Tidak menyimpan username, password, API key, atau kredensial provider.

## Route, Permission, dan UI

Tambahkan resource route:

| Method | Path | Permission |
|---|---|---|
| GET | `/internet-backups` | `internet_backups.view` |
| GET | `/internet-backups/{internetBackup}` | `internet_backups.view` |
| POST | `/internet-backups` | `internet_backups.create` |
| PUT/PATCH | `/internet-backups/{internetBackup}` | `internet_backups.update` |
| DELETE | `/internet-backups/{internetBackup}` | `internet_backups.delete` |

Implementasi:

- `InternetBackupController`
- `InternetBackup` model dengan `LogsModelActivity`
- migration tabel dan soft delete
- view daftar/detail/form mengikuti pola `IP Transit`
- DataTables/server-side search dan filter
- filter berdasarkan vendor, status, dan subscription/client
- dropdown subscription menampilkan kode subscription dan nama client
- kolom daftar: nama koneksi, client, subscription, vendor, bandwidth, biaya bulanan, status, aksi
- tombol tambah/edit/hapus mengikuti permission
- hapus menggunakan soft delete; record yang sudah dipakai tidak hilang permanen dari histori operasional
- validasi server-side menggunakan field yang sudah difilter, bukan `$request->all()`

Integrasi sidebar:

- Tambahkan menu **Internet Backup** di grup **Infrastruktur**
- Parent menu tampil jika user memiliki `internet_backups.view`
- Active state memakai `internet-backups.*`
- Tambahkan breadcrumb pada `AppLayout`
- Role default yang direkomendasikan: Owner, Admin, dan NOC
- Employee/role lain hanya mendapat akses jika diberikan melalui Manajemen Role

Integrasi Global Search:

- Cari berdasarkan nama koneksi, circuit ID, IP, gateway, vendor, kode subscription, dan nama client
- Hasil pencarian hanya muncul bila user memiliki `internet_backups.view`
- Detail search tidak menampilkan data sensitif karena modul tidak menyimpan kredensial

## Dokumentasi dan Audit

- Update `docs/modules/infrastructure.md` dengan tujuan, field, route, permission, relasi, lifecycle, dan batasan modul.
- Tambahkan `docs/modules/internet-backup.md` bila dokumentasi modul dipisahkan dari dokumentasi Infrastruktur.
- Update `docs/permission-matrix.md` untuk permission dan role default.
- Update `CHANGELOG.md` pada kategori `Added`.
- Tambahkan deployment note:
  - jalankan migration;
  - jalankan `PermissionSeeder`;
  - reset permission cache;
  - jalankan `php artisan optimize:clear` bila diperlukan.
- Jalankan audit:
  - `crm-permission-auditor`
  - `crm-activitylog-auditor`
  - `crm-doc-maintainer`
  - `graphify update` setelah seluruh kode/dokumentasi selesai.

## Testing

Tambahkan Feature Test untuk:

- user tanpa `internet_backups.view` menerima 403;
- user tanpa create/update/delete tidak dapat menjalankan aksi terkait;
- user berpermission dapat membuat, melihat, mengubah, dan menghapus data;
- vendor wajib berasal dari tabel `vendors`;
- subscription wajib berasal dari tabel `subscriptions` bila diisi;
- subscription terpilih hanya berasal dari layanan connectivity;
- status dan bandwidth tervalidasi;
- biaya provider menerima angka desimal non-negatif;
- backup berstatus `active` harus memiliki subscription;
- soft delete tidak menampilkan record pada daftar aktif;
- relasi client ditampilkan melalui subscription;
- activity log mencatat create/update/delete tanpa credential sensitif;
- global search hanya menampilkan hasil sesuai permission.

## Asumsi dan Batasan

- Nama teknis modul: `InternetBackup`; tabel: `internet_backups`; permission prefix: `internet_backups`.
- Provider memakai master `Vendor` yang sudah ada.
- Internet Backup bukan tipe `Service` atau `Package` baru pada MVP.
- Belum ada integrasi billing, monitoring provider, failover otomatis, router configuration, atau notifikasi outage.
- Data koneksi yang sudah `terminated` dipertahankan untuk histori dan tidak digunakan sebagai backup aktif.
