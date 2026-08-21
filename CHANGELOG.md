# Changelog

Semua perubahan penting pada project ini dicatat di file ini.

## 2026-08-21

### Fixed
- **Sinkronisasi detail domain SRS-X**: field `startdate` dan `enddate` dari endpoint `domain/info` kini dinormalisasi menjadi tanggal registrasi dan expired lokal. Sebelumnya tanggal dapat tetap kosong karena job hanya membaca nama field generik.
- **Halaman detail langganan**: memperbaiki ParseError Blade pada panel Domain ketika metadata registrar dimuat.

### Security
- **Metadata registrar SRS-X**: `authcode`/EPP dari respons `domain/info` kini dibuang sebelum disimpan. Migration `2026_08_21_000010` membersihkan metadata domain lama dan `2026_08_21_000011` menghapus key secret yang mungkin tercatat pada Activity Log oleh worker versi lama.

### Added
- **Detail registrar domain read-only**: panel Domain dapat menyinkronkan status, ID provider, nameserver, dan contact registrant/admin/billing/tech dari SRS-X. Contact hanya dibaca melalui API, disimpan sebagai metadata lokal, dan tidak ikut tercatat pada Activity Log.

### Changed
- **Daftar layanan domain**: nama domain kini menjadi informasi utama, diikuti nama dan ID pelanggan. Kolom tanggal menampilkan expired serta sisa hari; tanggal pemasangan tidak ditampilkan untuk layanan domain.

### Security (Audit Fase 2 — EPP / DNS retry)
- **P0 EPP code tidak lagi di payload queue**: `SetDomainEpp` kini hanya membawa `int $operationId`; nilai EPP disimpan terenkripsi di `registrar_operations.request_secret_encrypted` (migration `2026_08_20_000009`), dibaca + di-dekripsi saat job berjalan, lalu dihapus setelah sukses. Serialisasi job tidak mengandung secret (diuji). Kolom masuk `$hidden` + `activitylogExcludeAttributes` model `RegistrarOperation`.
- **P0 Retry DNS tidak kehilangan data record**: `EditDomainDnsRecord` menyimpan payload lengkap (`dnsid`, `record`, `type`, `destination`, `ttl`, `priority`) ke `request_payload_redacted`; retry `manage_dns` mengambil ulang payload lengkap dari operasi asli — record tidak berubah datanya.
- **P0 Idempotensi job mutasi**: state machine operasi mutasi sekarang `queued -> processing -> completed/failed`. Job melakukan claim atomik hanya dari `queued`; status `processing`, `failed`, dan `completed` tidak dapat mengirim request provider ulang. Retry manual mengubah `failed -> queued` secara atomik sebelum dispatch.
- **P2 Fixed route binding retry**: signature `retryOperation` kini menyertakan `SubscriptionDomain $domain` (sesuai pola method lain) — route param `{domain}` yang tidak dipakai di signature sebelumnya membuat Laravel 12 meresolusi argumen secara posisional sehingga `$operation` menerima string `'1'` (500 TypeError). Semua route param kini ter-bind ke model + verifikasi kepemilikan `operation.subscription_domain_id === domain.id`.

### Changed
- **Allowlist endpoint reseller SRS-X**: host API reseller resmi berbentuk `srb<angka>.srs-x.com` (contoh `srb168.srs-x.com`) kini diterima secara eksplisit. HTTPS, TLS verification, dan penolakan host di luar pola tetap berlaku.
- **P1 Permission `domains.set_epp` terpisah dari `domains.view_epp`**: user yang boleh *melihat* EPP tidak lagi otomatis bisa *mengganti* EPP. Dipakai di: middleware `SubscriptionDomainOperationController` (`fetchEpp` → `view_epp`, `setEpp` → `set_epp`), tombol UI `domain-actions.blade.php` (tombol retry `set_epp` digate `can_set_epp`), flag `$domainOps['can_set_epp']` di `SubscriptionController::show`, dan cek permission di `retryOperation` (case `set_epp`). Owner/Admin mendapat permission baru otomatis (seeder `PermissionSeeder`, re-run aman).
- **P1 Pemulihan operasi stale di job (worker timeout)**: trait `RecoverableRegistrarOperation` — operasi `processing` yang melewati `domain-registrars.operation_stale_minutes` (config + ENV `DOMAIN_REGISTRAR_OPERATION_STALE_MINUTES`, default 30 menit) ditandai `failed` (dengan `error_summary` jelas). Job lama tidak pernah melakukan retry otomatis; hanya retry manual yang dapat mengantrekan ulang operasi.
- **P1 Scheduled recovery otomatis**: command `registrar:recover-stale-operations` dijadwalkan tiap 5 menit (`routes/console.php`) untuk menandai semua operasi `processing` yang melewati stale window menjadi `failed` + mengirim notifikasi admin — memastikan recovery selalu berjalan meski worker timeout tanpa job retry.
- **P1 1001 per endpoint**: `SrsxResponseMapper::mapXml()` kini hanya `1000` success global; tambah `mapAvailability()` (check: `1000` available true, `1001` taken → success true available false), `mapDomainInfo()` (`1001` → not found false), `mapRegister()` (`1001` + `waiting for the complete document` → success pending_doc, `1001` + `Create Domain Failed` → failed), `mapTestConnection()` (`1000`/`1001` → koneksi OK). `SrsxApiClient::testConnection()` kini via `mapTestConnection` dan `registerDomain()` via `mapRegister`.
- **P1 Import dua akun**: tambah `RegistrarAccountController::manualImport()` (POST `registrar-accounts/{account}/import-manual`, validasi FQDN, cek TLD, deteksi konflik `LOWER(domain_name)`, buat `RegistrarOperation` `manual_import` `manual_review` dengan `idempotency_key` `md5(domains)`, flash result), view `registrar-accounts/show.blade.php` form paste domain; `SyncRegistrarAccountDomains` tetap `not_validated` dengan notifikasi manual import required, tidak tandai `manual_review` saat truncated.

### Added
- **Fase 3a permintaan perpanjangan domain**: Billing/staf berpermission `domains.renew` dapat mengajukan perpanjangan dengan konfirmasi nama domain dan durasi 1-10 tahun. Request disimpan sebagai `awaiting_approval`; Owner/Admin dengan permission baru `domains.approve_renew` dapat menyetujui menjadi `manual_review`. Alur ini tidak membuat tagihan, tidak mengantrekan job, dan tidak memanggil SRS-X sampai UAT mutasi serta kebijakan pembayaran disetujui.
- **Permission `domains.set_epp`**: permission baru `domains.set_epp` ditambahkan ke `PermissionSeeder`, Owner dan Admin otomatis dapat, UI `domain-actions.blade.php` ditambahkan gate pada tombol retry `set_epp`, module `domains` ditambahkan ke `$moduleGroups` di `RoleController` sehingga terlihat di Manajemen Role.
- **Command `registrar:recover-stale-operations`**: command baru di `app/Console/Commands/` yang menandai operasi registrar `processing` yang sudah stale menjadi `failed` + mengirim notifikasi admin; dijadwalkan tiap 5 menit di `routes/console.php`.
- **Trait `RecoverableRegistrarOperation`**: concern reusable di `app/Jobs/Concerns/` untuk pemulihan stale di job.
- **Service `RegistrarStaleRecovery`**: service utilitas di `app/DomainRegistrars/` untuk logika pemulihan (isStale, markFailed, staleCutoff).
- **Tests**: `RegistrarOperationTest` → 29 tests (baru: view_epp/set_epp permission separation ×1, scheduled recovery ×3, stale recovery ×3 jobs, retry set_epp redispatch).

### Deployment Notes
- Wajib: `php artisan migrate` untuk `2026_08_20_000009` + `php artisan db:seed --class=PermissionSeeder` (atau `migrate:fresh` untuk dev). Seeder aman re-run (`firstOrCreate`).
- Optional: `php artisan config:clear` setelah update `.env` untuk `DOMAIN_REGISTRAR_OPERATION_STALE_MINUTES`.
- Jalankan manual sekali: `php artisan registrar:recover-stale-operations` untuk memindahkan operasi processing lama ke failed.
- **P2**: mode tetap `read_only` di production; mutasi SRS-X belum teruji di domain UAT. Jangan aktifkan `managed` sebelum uji nameserver/DNS/EPP pada domain non-produksi yang disetujui.
