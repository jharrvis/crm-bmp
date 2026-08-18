# Changelog

Semua perubahan penting pada project ini dicatat di file ini.

## 2026-08-18

### Added
- Daftar user HestiaCP kini memiliki halaman detail read-only berisi pemakaian disk dan bandwidth, jumlah resource, rincian database beserta ukuran, serta pemakaian per web domain.
- Detail layanan Web Hosting kini menampilkan ringkasan pemakaian akun HestiaCP dan tautan ke rincian Infrastruktur bagi pengguna berizin.
- Form tambah dan edit layanan kini memakai dropdown bertahap: pilih jenis layanan dahulu, kemudian pilih paket yang sesuai.

### Security
- Detail user HestiaCP hanya memakai command API read-only. Password database dan credential akun tidak diambil atau ditampilkan.

### Fixed
- Memperbaiki ParseError pada detail layanan Web Hosting saat ringkasan pemakaian HestiaCP tersedia.

### Deployment Notes
- Tidak memerlukan migration atau seeder. Pastikan Access Key HestiaCP memiliki izin read-only `v-list-databases` untuk menampilkan rincian database.

## 2026-08-13

### Changed
- Form layanan Mail Hosting kini menawarkan domain yang sudah tercatat pada langganan domain pelanggan, dengan opsi input manual bila domain belum dikelola CRM. Domain dan server mail yang tersimpan kini dimuat kembali saat edit layanan.
- Detail Mail Hosting diringkas menjadi informasi layanan, server, pemakaian akun, dan akses ke halaman Kelola Mailbox. Daftar mailbox penuh dipusatkan pada Kelola Mailbox.
- Kelola Mailbox menampilkan pemakaian akun terkini, sinkronisasi metadata Zimbra saat halaman dibuka, serta pencarian alamat email live tanpa reload dan tanpa pull Zimbra berulang.
- Detail Mail Hosting kini memakai username dan credential admin dari konfigurasi Mail Server. Kelola Mailbox menampilkan pemakaian ruang aktual tiap akun dari Zimbra bila atribut `zimbraMailUsedQuota` tersedia.
- Server Mail Zimbra kini memiliki halaman detail dengan konfigurasi lokal, versi bila tersedia, daftar service aktif, status service, serta port service yang dibaca secara read-only dari Admin SOAP API.

### Security
- Integrasi Zimbra ditegaskan sebagai read-only: CRM tidak lagi memprovisikan domain, membuat, mengubah status, atau menghapus mailbox Zimbra, termasuk dari job yang telah lebih dahulu masuk antrean.
- Password admin mail hosting dapat disalin hanya oleh pengguna dengan permission `servers.manage`; aksesnya dicatat tanpa menyimpan credential pada Activity Log.

### Deployment Notes
- Jalankan `php artisan migrate` untuk menambahkan kolom pemakaian ruang mailbox.
- Jalankan `php artisan optimize:clear` dan restart queue worker setelah deploy agar job lama memakai aturan read-only terbaru.

## 2026-08-11

### Added
- Mail Hosting sekarang dapat menyinkronkan akun existing dari Zimbra ke daftar mailbox CRM melalui proses queue read-only. Akun hasil impor ditandai **Read-only dari Zimbra** sehingga tidak dapat disuspend, diaktifkan, atau dihapus secara tidak sengaja dari CRM.
- Permission `mailboxes.sync` ditampilkan pada group **Bisnis** di Manajemen Role untuk mengatur akses sinkronisasi mailbox.
- Daftar Mailbox sekarang menyediakan pencarian alamat email dan pagination agar domain dengan banyak akun tetap mudah dikelola.

### Changed
- Halaman detail subscription mail hosting dan Kelola Mailbox kini menarik metadata read-only dari Zimbra saat dibuka. Status akun, quota, dan display name lokal diperbarui tanpa membuat perubahan pada Zimbra.
- Domain layanan mail kini dijelaskan sebagai batas sinkronisasi: hanya alamat email pada domain layanan yang dapat ditampilkan dan ditautkan ke mailbox CRM.

### Security
- Penghapusan layanan mail hosting sekarang mengarsipkan layanan secara lokal, bukan menghapus record cascade. Data mailbox CRM serta akun/domain Zimbra dipertahankan, dan job Zimbra yang masih antre berhenti jika layanan tidak lagi aktif.

### Fixed
- Pencarian akun Zimbra untuk domain mail kini memakai filter `mail=*@domain`, sehingga akun existing pada domain layanan dapat ditemukan dengan benar.

### Deployment Notes
- Jalankan `php artisan migrate` untuk menambahkan penanda mailbox managed/read-only serta metadata sinkronisasi Zimbra.
- Jalankan `php artisan db:seed --class=PermissionSeeder` lalu `php artisan permission:cache-reset` untuk membuat permission `mailboxes.sync` tanpa menghapus permission role custom.
- Pastikan queue worker aktif, kemudian jalankan sinkronisasi dari halaman daftar mailbox layanan terkait.

## 2026-08-08

### Security
- Provisioning HestiaCP sekarang menolak username remote yang belum terbukti dibuat oleh CRM, memverifikasi email kontak utama, package target, dan kapasitas server sebelum membuat akun.
- Aksi lifecycle hanya tersedia untuk akun CRM yang sudah berhasil diprovisikan; akun linked/legacy tetap read-only.
- Penghapusan akun HestiaCP dibatasi untuk Owner, memerlukan pengetikan ulang username, dan aman terhadap retry setelah penghapusan remote berhasil.
- Response error HestiaCP tidak lagi ditulis mentah ke log aplikasi.

### Changed
- Perubahan status langganan tidak lagi otomatis suspend atau aktifkan akun HestiaCP. Automasi billing-to-hosting ditunda sampai aturan grace period dan pembayaran tersedia.
- Snapshot HestiaCP menggunakan lock agar refresh bersamaan tidak menghasilkan beberapa snapshot aktif.
- Form Server Hosting sekarang memisahkan credential HestiaCP (Access Key dan Secret Key) dari konfigurasi Zimbra, serta memakai payload form native agar create/update tersimpan konsisten.
- Tipe server dibatasi menjadi HestiaCP untuk web hosting, serta Zimbra dan Postfix untuk mail hosting. Postfix tersedia sebagai inventaris pending tanpa integrasi remote.
- Test koneksi HestiaCP sekarang memverifikasi command `v-list-users` dan memberikan diagnosis aman untuk masalah credential, permission, IP whitelist, endpoint, atau TLS.

### Added
- Modul **Manage Server Web Hosting (HestiaCP)**: konsol operasional berupa ringkasan snapshot server, daftar user live dengan cache singkat, test koneksi manual, refresh data, dan tautan user Hestia existing ke subscription hosting.
- Form tambah dan edit layanan hosting sekarang menyediakan pilihan untuk membuat akun HestiaCP baru atau menautkan user beserta domain yang sudah ada. Mode tautkan menyediakan pencarian user dari server HestiaCP, membatasi domain sesuai user terpilih, memverifikasi data remote, dan tidak menjalankan provisioning maupun perubahan akun.
- **Provisioning berbasis queue** untuk langganan hosting. Pembuatan user dan web domain diantrekan (`ProvisionHostingAccountJob`) dan ditandai idempoten; status `pending/provisioning/ready/failed/deleting/delete_failed` tercatat di `subscription_hostings`.
- Snapshot server `hosting_server_snapshots` dengan satu snapshot aktif per server (ringkasan JSON, status, waktu sinkron, pesan error).
- Lifecycle akun Hestia melalui queue: suspend/activate (`SetHostingAccountStatusJob`), reset password (`ResetHostingAccountPasswordJob`), dan hapus (`DeleteHostingAccountJob`) — hanya untuk akun yang benar-benar dikelola CRM (`managed_by_crm=true`).
- Adapter web hosting berbasis interface `WebHostingServerAdapter` + resolver `WebHostResolver`; `HestiaCPService` kini memakai `Http::timeout(30)` tanpa menonaktifkan verifikasi TLS secara global (diatur via `HESTIACP_VERIFY_SSL`).
- Permission baru `servers.manage`, `servers.provision`, `servers.suspend`, `servers.reset_password`, `servers.delete_user`. NOC hanya mendapat `servers.connect`, `servers.manage`, `servers.suspend` (tanpa delete).
- Field `hestia_package` pada paket dan migration unik/index baru pada `subscription_hostings` (preflight pendeteksi duplikat legacy sebelum migrasi).

### Fixed
- Provisioning domain Zimbra kembali berjalan karena HTTP client menggunakan `timeout()` yang didukung Laravel, bukan method `withTimeout()` yang tidak tersedia.
- Form layanan tidak lagi memuat group Zabbix untuk paket non-konektivitas. Jika Zabbix gagal diakses pada layanan konektivitas, pesan konfigurasi atau API yang aman kini ditampilkan.
- `SubscriptionController` tidak lagi menyimpan `SubscriptionHosting` saat panggilan `v-add-user` gagal; akun dibuat dengan status `pending` lalu diprovisikan via queue setelah commit.
- Pemanggilan Hestia langsung dari request browser digantikan job queue agar tidak memblokir respons pada operasi lambat.
- `SubscriptionHosting` kini memakai `LogsModelActivity`, encrypted cast, dan `$hidden` untuk `password_encrypted`; credential tidak ikut terserialisasi.
- Nonaktif/terminate langganan dan reset password pada layanan hosting kini diantrekan, bukan dipanggil sinkron.

### Changed
- Username baru hosting divalidasi `^[a-z][a-z0-9_]{0,31}$`; operasi aksi memakai `username` pada request body, bukan parameter route, agar aman terhadap aturan username Hestia/Linux.
- Halaman Manage Server tersedia dari daftar server web (`servers.index`) untuk server tipe HestiaCP aktif.

### Deployment Notes
- Jalankan `php artisan migrate` (migration 2026_08_08_000006 s.d. 000008).
- Jalankan `php artisan db:seed --class=PermissionSeeder` lalu `php artisan permission:cache-reset`.
- Pastikan queue worker active (`php artisan queue:work`) agar provisioning dan lifecycle hosting berjalan.
- Sebelum mengaktifkan provisioning: buat Hestia access/secret key minimum, whitelist IP server CRM, uji test connection lalu create/link/suspend pada akun non-production.

---

### Added
- Modul baru **Mail Hosting** dengan integrasi server Zimbra (SOAP Admin API). Admin dapat menambahkan layanan mail pada paket berjenis `mail`, memilih server Zimbra, dan mengantrekan provisioning domain secara aman.
- Manajemen **mailbox** per langganan: buat account, quota, aktif/nonaktif (suspend/activate), dan hapus melalui queue. Akses melalui detail layanan pelanggan bertipe mail.
- Field paket untuk kotak surat (max_mailboxes, mailbox_quota_mb, alias_max) dan field `api_endpoint` pada Hosting Server tipe `zimbra`.
- Permission baru `mailboxes.*` (view, create, update, delete, suspend) untuk membatasi akses mailbox.

### Fixed
- Credential Zimbra, password mailbox, dan password admin mail tidak lagi ikut terkirim pada respons JSON atau activity log.
- Validasi mail hosting kini hanya menerima server Zimbra aktif, domain valid, domain unik per server, alamat mailbox unik, serta quota yang tidak melebihi paket langganan.
- Edit layanan tidak dapat mengganti domain atau server mail setelah mailbox dibuat. Suspend/terminate layanan kini mengantrekan suspend mailbox terkait secara aman.
- `PermissionSeeder` tidak lagi memakai `syncPermissions()` untuk role default, sehingga permission yang sudah dikonfigurasi di production tidak dicabut saat seeder dijalankan ulang.
- Form edit layanan sekarang menyimpan perubahan paket, harga paket terkunci, periode billing, pajak, tanggal billing, dan detail teknis ke database.
- Perhitungan prorata perubahan paket kini memakai harga serta periode paket lama yang benar.
- Perubahan paket antar jenis layanan diblokir dengan validasi yang jelas untuk mencegah data konektivitas, hosting, atau domain tidak konsisten.

### Changed
- Mail Hosting tidak lagi menjadi menu sidebar terpisah; layanan ini tampil melalui group **Layanan Pelanggan** berdasarkan master layanan yang dipilih.
- Katalog Layanan sekarang menyediakan navigasi **Paket Email Hosting**. Admin dapat membuat layanan bertipe `mail` di Master Layanan, lalu membuat paket mailbox, quota, dan batas alias.
- Infrastruktur memisahkan tampilan **Server Web Hosting** dan **Server Mail Hosting** menggunakan filter tipe pada entitas server yang sama.

### Deployment Notes
- Jalankan `php artisan migrate` untuk membuat tabel `subscription_mail_hostings` dan `mailboxes`, menambahkan kolom `api_endpoint` dan field paket mail, serta constraint/provisioning status (migration 2026_08_08_000001 s.d. 000005).
- Jalankan `php artisan db:seed --class=PermissionSeeder` lalu `php artisan permission:cache-reset` untuk permission Mail Hosting.
- Pastikan queue worker production aktif (`php artisan queue:work`) agar domain dan mailbox diprovisikan.
- Untuk integrasi Zimbra, isi `api_endpoint`, `username`, dan `secret_key` (password admin Zimbra) pada tiap Hosting Server tipe `zimbra`.

## 2026-08-07

### Fixed
- Form tambah dan edit layanan sekarang selalu mengirim tanggal pemasangan yang dipilih serta menampilkan validasi langsung jika belum diisi.
- Section Metro Ethernet sekarang hanya tampil dan ikut diproses untuk layanan konektivitas; layanan hosting dan domain hanya menampilkan field yang relevan. Detail domain kini disimpan pada data langganan domain.
- Pembuatan langganan yang menghasilkan invoice prorata tidak lagi gagal karena model `Invoice` dan `InvoiceItem` belum di-import pada controller.
- Rekonsiliasi kode pelanggan sekarang mengalokasikan nomor urut kosong berikutnya saat kode target sudah dipakai, tanpa menimpa kode pelanggan lain.

### Added

- Form pelanggan sekarang menyediakan pencarian lokasi berdasarkan nama jalan, area, kecamatan, atau kota untuk memusatkan peta dan mengisi koordinat dengan lebih cepat.
- Modul Infrastruktur baru `IP Transit` untuk mengelola vendor, CID, IP Address, IP Gateway, AS Number, dan bandwidth. Modul ini tersedia di menu Infrastruktur dan Global Search.
- Halaman detail untuk Metro Ethernet dan IP Transit, termasuk tombol lihat dari daftar data.
- Command `clients:reconcile-codes` untuk simulasi dan koreksi aman prefix `client_code` sesuai `branch_id`, dengan laporan audit, deteksi konflik, transaction, dan kode sementara untuk menjaga unique constraint.

### Changed

- Pencarian lokasi pelanggan diproses dari server, dibatasi kecepatannya, dan menggunakan cache agar tidak mengirim pencarian otomatis saat pengguna mengetik.
- IP Transit sekarang memiliki nama koneksi dan form lebar dua kolom agar parameter jaringan dapat diisi tanpa scroll panjang.

### Fixed

- `PermissionSeeder` tidak lagi mencoba menetapkan permission untuk role legacy `Client`, sehingga aman dijalankan pada production yang memakai akun portal client terpisah.
- Tanggal registrasi pelanggan sekarang kembali tampil dan dapat diperbarui pada form edit.
- Quick View Global Search tidak lagi membuka modal edit Metro Ethernet ketika dipakai dari halaman Metro Ethernet.
- Tombol tutup, klik backdrop, dan tombol Escape pada Quick View Global Search kembali berfungsi setelah fungsi modal aplikasi diekspos untuk handler halaman.
- Penyimpanan perubahan langganan tidak lagi gagal karena `ProrataCalculationService` menggunakan namespace yang salah.

### Deployment Notes

- Jalankan `php artisan migrate` untuk membuat tabel `ip_transits`.
- Jalankan `php artisan db:seed --class=PermissionSeeder` lalu `php artisan permission:cache-reset` untuk permission IP Transit.
- Tambahkan `MAP_NOMINATIM_USER_AGENT` di `.env`, lalu jalankan `php artisan config:clear`.

## 2026-08-06

### Added

- Tipe pelanggan sekarang mendukung kategori Bisnis, Pemerintah, Pendidikan, Nirlaba, Keagamaan, Komunitas, Properti Bersama, dan Lainnya dengan kategori custom.
- Form pelanggan sekarang memiliki tab alamat dengan pilihan provinsi, kabupaten/kota, kecamatan, dan kelurahan/desa yang dapat dicari dan saling memfilter. RT/RW, kode pos, serta koordinat dapat diisi sebagai pelengkap.
- Form pelanggan sekarang menyediakan modal peta OpenStreetMap untuk memilih titik dan mengisi latitude/longitude tanpa Google Maps API key atau billing account.

### Changed

- Form, daftar, detail pelanggan, dan panel informasi langganan sekarang menampilkan label tipe pelanggan yang konsisten.
- Data alamat lama pelanggan tetap dipertahankan; struktur alamat administratif baru bersifat opsional dan dapat dilengkapi bertahap.
- Default wilayah Jawa Tengah per cabang sekarang diterapkan otomatis saat pelanggan belum memiliki kode provinsi dan kabupaten/kota; default tersebut juga menjadi pusat awal peta.

### Deployment Notes

- Jalankan `php artisan migrate` untuk menambahkan field kategori pelanggan custom.
- Jalankan `php artisan db:seed --class=AdministrativeAreaSeeder` satu kali setelah migration untuk mengimpor data wilayah lokal yang dipakai dropdown alamat.
- Jalankan `php artisan migrate` untuk menambahkan default wilayah dan titik pusat peta pada cabang.

## 2026-07-27

### Added

- Halaman detail Router menampilkan identitas perangkat, kredensial yang dapat ditampilkan/disalin, serta daftar langganan yang terhubung. Hasil Router di Pencarian Global sekarang langsung membuka halaman ini.
- Router sekarang dapat diberi peran opsional melalui pilihan standar atau nilai custom.

### Changed

- Form tambah dan edit Router sekarang menyediakan kontrol untuk menampilkan/menyembunyikan serta menyalin password router, tanpa menyimpan password ke Activity Log.
- Daftar Router sekarang menyediakan aksi lihat detail sebelum edit dan hapus.
- Modal Router diperlebar dan ditata dalam grid dua kolom pada desktop agar seluruh form dan aksi simpan lebih mudah dijangkau tanpa scroll panjang.

### Deployment Notes

- Jalankan `php artisan migrate` untuk menambahkan field peran router opsional.

## 2026-07-10

### Changed

- Fitur register user publik sekarang dinonaktifkan secara default karena akun staff dan client hanya dibuat oleh admin.
- Route, halaman, dan link UI register sekarang tidak bisa diakses saat `AUTH_REGISTRATION_ENABLED=false`, tetapi implementasinya tetap disimpan untuk kebutuhan aktivasi di masa depan.

## 2026-07-01

### Added
- **Sistem Pengingat Tagihan Otomatis** (`InvoiceReminder`): Mengirim email peringatan sebelum jatuh tempo dan pemberitahuan overdue secara otomatis setiap jam 08:00 pagi.
- Jadwal reminder mengikuti pengaturan global (`billing.reminder_days_before` dan `billing.reminder_days_after`).
- **PDF Invoice**: Download PDF invoice via DomPDF, dengan template khusus `invoices/pdf.blade.php` yang kompatibel untuk cetak A4.
- **Client Portal Payment Confirmation**: Client dapat mengirim konfirmasi pembayaran via endpoint API portal.
- **Laporan Keuangan** (`FinancialReportController`): Halaman ringkasan pendapatan, tagihan, piutang, aging report, dan daftar pembayaran terbaru per periode.
- Menu sidebar **Laporan Keuangan** untuk akses cepat ke halaman report.

### Changed
- **Email Queue**: Pengiriman email tagihan sekarang dilakukan secara asynchronous melalui queue untuk mencegah proses request yang lama/blocking.
- **Normalisasi WhatsApp**: Nomor WhatsApp tujuan sekarang dinormalisasi (mengubah awalan `0` menjadi `62`) agar API web `wa.me` berfungsi dengan baik saat membuka link chat.
- **Invoice Delivery Mail** sekarang menggunakan `ShouldQueue` untuk pengiriman asynchronous.
- **SystemSetting** menggantikan helper `setting()` — semua pemanggilan di controller, model, job, dan blade view menggunakan `\App\Models\SystemSetting::get()`.

### Fixed
- Race condition pada `generateInvoiceNumber()`: sekarang menggunakan database transaction + `lockForUpdate()`.
- Helper `setting()` tidak reliable di production (error autoload), diganti dengan method statis SystemSetting.

### Deployment Notes
- Jalankan `composer update` untuk menginstal `barryvdh/laravel-dompdf`.
- Jalankan `php artisan migrate` untuk tabel `invoice_reminders`.
- Jalankan `php artisan queue:restart` jika queue worker sedang berjalan.
- Konfigurasi queue worker untuk production: `php artisan queue:work`.

## 2026-06-30

### Added

- **Sistem Pengaturan Global** (`SystemSettings`): konfigurasi yang bisa diubah via UI untuk billing (PPN, PPh23, due days, tanggal generate, prorata toggle, reminder schedule).
- **Modul Pembayaran** (`Payments`): mencatat pembayaran untuk tagihan. Mendukung status `pending`, `verified`, dan `rejected`.
- **Status Invoice `partially_paid`**: status baru saat pembayaran kurang dari total tagihan.
- **Prorata Kalkulator**: penghitungan proporsional untuk tagihan saat registrasi baru, upgrade/downgrade, dan penghentian layanan di tengah siklus bulan.
- **Job Auto Generate Tagihan Bulanan**: cron job (`GenerateMonthlyInvoices`) yang otomatis membuat tagihan setiap bulan pada tanggal yang ditentukan di pengaturan.
- **Job Auto Overdue**: cron job (`MarkOverdueInvoices`) yang menandai status `overdue` secara otomatis.

### Changed

- Tarif pajak (PPN dan PPh23) tidak lagi hardcoded, melainkan mengambil nilai dari `SystemSettings`.
- Data tax breakdown sekarang disimpan pada saat tagihan bulanan dibuat secara otomatis, sehingga item tagihan konsisten dengan total tagihan.
- Penomoran tagihan sekarang mendukung lock level database (transaction lock for update) untuk mencegah nomor kembar.
- Halaman daftar tagihan sekarang di-load menggunakan server-side pagination, tidak lagi meload seluruh data sekaligus.

## 2026-06-29

### Changed

- `AGENTS.md` ditambahkan Section 19: Standar Keamanan Kode, mencakup input/output escaping, authentication & authorization, data protection & enkripsi, secret management, API security, file upload, dependency security, dan mass assignment protection.

## 2026-06-29

### Changed

- `AGENTS.md` disempurnakan: tambah standar kode teknis (naming convention, arsitektur, validation, error handling), standar queue/job, standar testing, standar branching, standar environment variables. Numbering section diperbaiki. Daftar modul disinkronkan dengan kode aktual. Referensi skill operasional diupdate dengan lokasi yang benar. Tambah section known permission gaps.
- `docs/README.md` diupdate dengan index modul lengkap.

### Added

- Dokumentasi modul `Clients` di `docs/modules/clients.md`: data pelanggan, kontak PIC, portal account, alur bisnis, permission matrix.
- Dokumentasi modul `Subscriptions` di `docs/modules/subscriptions.md`: langganan connectivity/hosting/domain, pricing model, integrasi Zabbix/HestiaCP, known issues.
- Dokumentasi modul `Tickets` di `docs/modules/tickets.md`: tiket support, queue system, canned response, SLA tracking, bulk update, integrasi client portal.
- Port 5 CRM skills ke opencode (`crm-doc-maintainer`, `crm-release-checker`, `crm-api-doc-writer`, `crm-permission-auditor`, `crm-activitylog-auditor`).

## 2026-06-27

### Added

- Fitur upload foto profil dengan preview sebelum simpan di halaman profil
- Tab `Tagihan` di halaman detail pelanggan dengan ringkasan dan daftar invoice terkait, dilindungi permission `invoices.view`

### Changed

- Halaman daftar role sekarang hanya menampilkan jumlah user per role, bukan daftar user. Daftar user lengkap tetap tersedia di halaman detail role.
- Thread percakapan pada halaman detail ticket sekarang ditampilkan sebagai bubble chat kanan/kiri untuk membedakan pesan client, staff, dan internal note dengan lebih jelas.

## 2026-06-26

### Added

- Halaman khusus `Buat Invoice Manual` sebagai pengganti drawer/modal di daftar invoice
- Modul dokumentasi `Invoices` di `docs/modules/invoices.md`
- Dokumentasi modul `Activity Log` di `docs/modules/activity-log.md`

### Changed

- Tombol `Buat Invoice Manual` di daftar invoice sekarang menuju halaman create khusus
- Halaman daftar invoice difokuskan kembali hanya untuk overview, filter, tabel, dan aksi invoice
- Form invoice manual sekarang mendukung pencarian pelanggan, tanggal invoice, preset jatuh tempo, ringkasan subtotal/PPN/diskon, tanda tangan, draft/final/send action, dan modal kirim email/WhatsApp
- Form invoice manual sekarang menempatkan catatan di kiri, ringkasan tagihan di kanan, serta area tanda tangan tepat di atas tombol simpan
- Daftar invoice sekarang menampilkan status draft, indikator invoice sudah terkirim atau belum, serta aksi edit, kirim ulang, print, dan download PDF dari dropdown aksi
- Pembuatan dan update invoice yang memilih kanal WhatsApp sekarang kembali ke daftar invoice lalu langsung membuka link `wa.me`
- Halaman detail invoice sekarang dapat menampilkan tanda tangan dan mendukung mode print otomatis

### Documentation

- TODO dokumentasi sekarang mencakup metode penghapusan `activity log` berkala menggunakan `php artisan activitylog:clean`

### Deployment Notes

- Jalankan `php artisan migrate` untuk field invoice manual baru
- Jalankan `npm run build` jika deploy memakai aset hasil build Tailwind
- Pastikan `php artisan storage:link` tersedia bila signature invoice diakses dari disk `public`

## 2026-06-25

### Added

- Entry point `global search` di header aplikasi untuk pencarian lintas modul
- Halaman `Pembaruan Sistem` yang membaca `CHANGELOG.md` dan menampilkan commit terbaru dari GitHub repo
- Halaman `Activity Log` global berbasis `spatie/laravel-activitylog`
- Halaman `Dokumentasi` di dalam aplikasi yang membaca file markdown dari folder `docs/`
- Struktur awal dokumentasi `docs/` untuk deployment, permission matrix, modul, dan API portal client
- Placeholder halaman `Pengaturan` untuk rencana konfigurasi sistem ke depan
- Group menu baru `Sistem` di sidebar
- Standar kerja repo melalui `AGENTS.md` untuk dokumentasi, changelog, permission, deployment note, dan API

### Changed

- `Manajemen Role` dipindahkan ke group menu `Sistem`
- Struktur permission role dikelompokkan ulang agar modul `Sistem` tampil konsisten di halaman edit role
- Sidebar disusun ulang agar menu `Pembaruan Sistem`, `Dokumentasi`, `Activity Log`, `Manajemen Role`, dan `Pengaturan` berada dalam satu kelompok administratif
- Role management sekarang dapat menampilkan siapa saja user yang memakai setiap role
- Timezone aplikasi sekarang mengikuti `APP_TIMEZONE` agar activity log dan timestamp internal bisa memakai region yang benar
- Global search diperluas ke hampir semua modul data yang dapat diakses user, termasuk organisasi, pelanggan, billing, support, infrastruktur, layanan, dan paket
- Global search sekarang memiliki halaman hasil pencarian penuh dengan filter modul dan link `Lihat semua hasil` dari dropdown header
- Global search sekarang mendukung `quick view` untuk modul operasional seperti pelanggan, router, server, vendor, metro ethernet, cabang, divisi, karyawan, layanan, dan paket tanpa perlu pindah halaman

### Fixed

- Inkonsistensi penempatan menu administratif yang sebelumnya tersebar di `Master Data`
- Jam pada aplikasi yang sebelumnya selalu mengikuti `UTC` karena timezone config masih hardcoded
- Legacy role `Client` dan user dummy `Pelanggan A` yang sebelumnya muncul di manajemen role walaupun portal client sudah memakai tabel akun terpisah
- Overlay modal sekarang dipindahkan ke `document.body` agar backdrop benar-benar fullscreen dan tidak bergeser oleh container layout

## 2026-06-24

### Added

- Seeder akun `webmaster@bmp.net.id` dengan role `Owner` untuk akses penuh sistem
- Permission baru untuk modul infrastruktur: `vendors`, `metro_ethernets`, dan `zabbix_monitors`
- Asset dekoratif SVG untuk panel kanan halaman auth

### Changed

- Halaman `login` dan `forgot password` didesain ulang ke layout split dua kolom yang lebih modern
- Panel kanan auth disesuaikan agar lebih dekat ke referensi TailAdmin, dengan latar biru tua dan grid dekoratif
- Manajemen role diselaraskan dengan visibilitas menu dan akses route berbasis permission
- Default permission role `NOC` diperluas agar dapat melihat modul infrastruktur yang relevan

### Fixed

- Menu `Infrastruktur` yang sebelumnya tidak muncul untuk role `NOC` walaupun permission sudah ada
- Inkonsistensi modul `Organisasi`, `Infrastruktur`, dan `Manajemen Role` yang sebelumnya masih dikunci berdasarkan nama role

## 2026-05-16

### Added

- Toggle `PPh23 2%` pada billing langganan
- `registered_at` untuk pelanggan
- Seeder import pelanggan internet cabang Kudus
- Breakdown pajak dan terbilang bahasa Indonesia pada invoice

### Changed

- Format `client_code` pelanggan diubah ke `{branch_id}{yy}{sequence}`
- Form input/edit layanan dipecah menjadi tab `Umum`, `Billing`, dan `Teknis`
- Layout invoice disesuaikan mengikuti format operasional BMP.NET
- Footer invoice dan mode print dioptimalkan agar lebih rapi di kertas A4

### Fixed

- Animasi toggle pajak pada form layanan
- Label pajak pada ringkasan billing
- Terbilang invoice yang sebelumnya menambahkan `nol` di akhir nominal bulat
- Invoice cabang yang sebelumnya masih memakai alamat hardcoded

## 2026-05-15

### Added

- Seeder backfill `client_code` pelanggan existing
- Logika generate kode pelanggan baru berbasis branch dan tahun registrasi
- Opsi `PPN 11%` pada langganan

### Changed

- Generator invoice otomatis memakai harga dasar layanan untuk item invoice baru agar breakdown pajak lebih konsisten

### Fixed

- Sinkronisasi total tagihan `harga jual + PPN - PPh23`
