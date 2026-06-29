# AGENTS.md

Panduan kerja standar untuk pengembangan `BMPnet CRM`.

Dokumen ini dibuat agar setiap perubahan di repo dilakukan secara sistematis, terdokumentasi, dan mudah diaudit.

## 1. Tujuan

Setiap pekerjaan di repo ini harus memenuhi empat hal:

1. Perubahan kode jelas tujuannya.
2. Dampak ke modul lain diketahui.
3. Dokumentasi ikut diperbarui.
4. Riwayat perubahan bisa dibaca oleh tim teknis dan non-teknis.

## 2. Sumber Kebenaran

Gunakan urutan berikut sebagai acuan utama:

1. `AGENTS.md`
2. `README.md`
3. `CHANGELOG.md`
4. Dokumen modul di folder `docs/` atau dokumen lain yang nanti ditambahkan
5. Kode aplikasi

Jika dokumentasi dan kode tidak sinkron:

- anggap kode adalah kondisi aktual
- perbarui dokumentasi pada pekerjaan yang sama

## 3. Artefak Dokumentasi yang Wajib Dijaga

### 3.1 Dokumen global

- `README.md`
  - ringkasan aplikasi
  - cara menjalankan project
  - daftar fitur utama
- `CHANGELOG.md`
  - perubahan per tanggal atau per rilis
  - gunakan kategori `Added`, `Changed`, `Fixed`, `Removed`, `Security`
- `AGENTS.md`
  - standar kerja pengembangan
- `docs/`
  - sumber dokumentasi modular dan API yang dibaca dari repo

### 3.1.1 Dokumentasi di dalam aplikasi

Dokumentasi tidak cukup hanya ada sebagai file repo. Untuk kebutuhan operasional internal:

- dokumentasi utama tetap disimpan sebagai file markdown di repo
- aplikasi harus bisa menampilkan dokumentasi tersebut dalam UI
- halaman dokumentasi di aplikasi hanya menjadi pembaca dari file repo, bukan sumber kebenaran terpisah

Jika modul `Dokumentasi` ada di aplikasi:

1. sumber isi harus berasal dari folder `docs/`
2. akses halaman harus dijaga oleh permission yang jelas
3. dokumentasi API portal/client harus ikut tampil di sana
4. perubahan dokumentasi tetap wajib melalui git

### 3.2 Dokumen modul

Ke depan, setiap modul utama sebaiknya punya dokumen sendiri, minimal berisi:

- tujuan modul
- route utama
- controller/model/view terkait
- permission yang dipakai
- alur bisnis
- ketergantungan ke modul lain
- catatan penting atau known limitation

Daftar modul yang harus dipetakan:

- Dashboard
- Global Search
- Cabang (Branch)
- Divisi
- Karyawan (Employee)
- Manajemen Role
- Clients / Pelanggan
- Client Contacts
- Client Portal Account Management
- Subscriptions / Langganan
- Services
- Packages
- Invoices / Tagihan
- Payments / Laporan Keuangan
- Tickets
- Ticket Canned Responses
- Infrastructure
  - Routers
  - Hosting Servers
  - Vendors
  - Metro Ethernet
  - Zabbix Monitors
- Network Topology
- System Updates
- Activity Log
- Settings
- Client Portal
- Profile

### 3.2.1 Known Permission Gaps

Berikut permission yang sudah didefinisikan di `PermissionSeeder` tapi belum ada implementasi (model/controller/view):

- `payments` (view, create, update, delete, verify)
- `financial_reports` (view)
- `work_orders` (view, create, update, delete, assign, complete)
- `towers` (view, create, update, delete)
- `odps` (view, create, update, delete)

Permission ini dipertahankan di seeder sebagai placeholder untuk implementasi mendatang. Jangan hapus tanpa keputusan eksplisit.

### 3.3 Dokumen API

Karena aplikasi ini terhubung ke portal client melalui API, endpoint harus terdokumentasi dengan baik.

Minimal setiap dokumen API harus memuat:

- nama endpoint
- method
- path
- kebutuhan auth
- request body / query parameter
- response success
- response error utama
- modul atau fitur yang memakai endpoint itu
- catatan versi atau kompatibilitas jika ada

Struktur yang disarankan:

- `docs/api/client-portal-auth.md`
- `docs/api/client-portal-dashboard.md`
- `docs/api/client-portal-subscriptions.md`
- `docs/api/client-portal-invoices.md`
- `docs/api/client-portal-tickets.md`
- `docs/api/client-portal-notifications.md`
- `docs/api/openapi.yaml` jika nanti ingin distandarkan lebih formal

## 4. Aturan Sebelum Mengubah Kode

Sebelum implementasi:

1. Identifikasi modul yang disentuh.
2. Cek controller, model, route, view, seeder, migration, dan permission terkait.
3. Tentukan apakah perubahan termasuk:
   - fitur baru
   - perubahan alur
   - bug fix
   - perubahan data
   - perubahan permission/role
   - perubahan UI/UX
4. Tentukan dokumentasi apa yang wajib ikut diupdate.

## 5. Aturan Setelah Mengubah Kode

Setelah implementasi:

1. Update `CHANGELOG.md` jika perubahan memengaruhi perilaku aplikasi, fitur, UI, role, data, atau deployment.
2. Update `README.md` jika perubahan memengaruhi onboarding, setup, atau kemampuan utama aplikasi.
3. Update dokumen modul jika perubahan menyentuh alur bisnis, permission, route, atau integrasi.
4. Tambahkan catatan migrasi/seeder jika production membutuhkan langkah manual.
5. Jika perubahan menyentuh API, update dokumen endpoint terkait di `docs/api/`.

## 6. Standar Isi Changelog

Setiap entri changelog sebaiknya menjawab:

- fitur apa yang berubah
- modul apa yang terdampak
- apakah ada langkah deploy tambahan
- apakah ada perubahan permission/role
- apakah ada migration atau seeder yang wajib dijalankan

Contoh format:

```md
## 2026-06-25

### Added
- Menambahkan halaman Activity Log global.

### Changed
- Role management sekarang menampilkan daftar user per role.

### Fixed
- Timezone aplikasi sekarang configurable melalui `APP_TIMEZONE`.

### Deployment Notes
- Jalankan `php artisan config:clear` setelah update environment.
```

## 7. Standar Dokumentasi Modul

Jika nanti dibuat dokumen per modul, gunakan template ini:

```md
# Nama Modul

## Tujuan

## Entitas Terkait

## Route Utama

## Permission

## Alur Bisnis

## Integrasi Modul Lain

## Seeder / Migration Terkait

## Known Issues / Catatan
```

Untuk dokumen API, gunakan template minimum ini:

```md
# Nama API

## Tujuan

## Authentication

## Endpoint

### METHOD /path

#### Request

#### Response Success

#### Response Error

## Dipakai Oleh

## Catatan
```

## 8. Standar Permission dan Role

Setiap modul baru harus menjelaskan:

- menu tampil untuk permission apa
- route dijaga oleh permission apa
- role default mana yang mendapat permission itu
- apakah perubahan perlu update `PermissionSeeder`

Jika ada menu baru atau modul baru:

1. tambahkan permission-nya
2. tampilkan di `Manajemen Role`
3. pastikan sidebar mengikuti permission, bukan hardcoded role
4. dokumentasikan mapping default role jika ada
5. jika modul itu bersifat referensi internal, pertimbangkan juga apakah perlu tampil di halaman `Dokumentasi`

## 9. Standar Audit dan Activity Log

Semua aksi penting harus diupayakan tercatat:

- login / logout
- create / update / delete entitas utama
- perubahan role user
- perubahan permission role
- perubahan status invoice / payment / ticket
- perubahan konfigurasi sistem

Jika suatu aksi penting belum dilog:

- tambahkan ke backlog dokumentasi modul
- utamakan implementasi pada pekerjaan terkait berikutnya

## 10. Standar UI/UX

Untuk perubahan UI:

- jelaskan halaman yang berubah
- jelaskan alasan perubahan
- pastikan konsisten dengan pola visual aplikasi
- jika ada pola reusable baru, usahakan bisa dipakai modul lain

Perubahan UI yang layak dicatat di changelog:

- revamp halaman utama modul
- perubahan alur filter/search/list
- perubahan modal/action penting
- perubahan invoice printable
- perubahan auth screen

## 11. Standar Kode Teknis

### 11.1 Naming Convention

- **Controller**: singular PascalCase (`ClientController`, `InvoiceController`)
- **Model**: singular PascalCase (`Client`, `Invoice`, `InvoiceItem`)
- **Migration**: snake_case dengan verb (`create_invoices_table`, `add_ppn_fields_to_subscriptions_table`)
- **View folder**: plural kebab-case (`invoices/`, `clients/`, `activity-logs/`)
- **Route**: plural kebab-case (`/invoices`, `/metro-ethernets`)
- **Permission**: `module.action` format (`invoices.view`, `clients.create`, `tickets.assign`)

### 11.2 Arsitektur Kode

- Logic bisnis yang kompleks atau reusable harus dipindah ke `app/Services/`.
- Controller hanya menangani HTTP concern: validasi input, memanggil service/model, mengembalikan response/view.
- Jika logic di controller melebihi ~50 baris untuk satu method, pertimbangkan untuk extract ke service class.
- Gunakan Eloquent relationship untuk query, hindari raw query kecuali untuk kebutuhan performance kritis.

### 11.3 Validation

- Gunakan `FormRequest` untuk validasi yang kompleks atau dipakai di lebih dari satu tempat.
- Inline `$request->validate()` boleh dipakai untuk validasi sederhana.
- Pesan error harus dalam Bahasa Indonesia jika user-facing.

### 11.4 Error Handling

- Gunakan `try-catch` pada operasi database transaction.
- Return response yang konsisten: redirect dengan flash message untuk web, JSON untuk API.
- Jangan expose detail error internal ke user (gunakan generic message + log detail).

### 11.5 Konfigurasi

- Jangan hardcode value yang bisa berubah (tax rate, due days, API URL, dll).
- Gunakan `config/*.php` yang membaca dari `.env`.
- Setiap file config baru harus punya default yang aman.

### 11.6 Model

- Setiap model bisnis utama harus menggunakan trait `LogsModelActivity` untuk audit trail.
- Definisikan `$fillable` secara eksplisit, jangan gunakan `$guarded = []`.
- Definisikan `$casts` untuk field yang memerlukan type casting (date, decimal, boolean).

## 12. Standar Queue dan Job

### 12.1 Kapan Menggunakan Queue

Gunakan queue untuk:
- pengiriman email dan notifikasi
- PDF generation
- bulk operations (generate invoice bulanan, blast WhatsApp)
- API call ke service eksternal (WhatsApp API, Zabbix API)
- operasi yang memakan waktu lebih dari 5 detik

Jangan gunakan queue untuk:
- operasi yang user butuh hasilnya langsung (CRUD response, redirect)
- validasi input
- operasi database sederhana

### 12.2 Konvensi Job

- Lokasi: `app/Jobs/`
- Naming: PascalCase, Verb + Noun (`GenerateMonthlyInvoices`, `SendInvoiceReminder`, `MarkOverdueInvoices`)
- Implement `ShouldQueue` interface
- Gunakan `tries` dan `backoff` property untuk retry strategy
- Log hasil eksekusi (berhasil/gagal) ke activity log jika relevan

### 12.3 Scheduled Jobs

- Daftarkan di `routes/console.php`
- Dokumentasikan jadwal di dokumen modul terkait
- Pastikan job idempotent (aman dijalankan ulang)

### 12.4 Development

- `composer dev` sudah menjalankan queue worker secara otomatis
- Production harus menjalankan `php artisan queue:work` sebagai persistent process

## 13. Standar Testing

### 13.1 Framework

- Gunakan PHPUnit (`composer test`)
- Lokasi: `tests/Feature/` untuk HTTP/integration tests, `tests/Unit/` untuk logic murni

### 13.2 Kapan Wajib Test

- Logic kalkulasi (billing, tax, pricing, discount)
- Service class baru
- API endpoint (request/response contract)
- Perubahan logic yang berpotensi regresi tinggi

### 13.3 Kapan Opsional

- CRUD sederhana yang hanya proxy ke Eloquent
- View rendering
- Perubahan UI-only (CSS, layout)

### 13.4 Konvensi

- Naming: snake_case deskriptif (`test_can_create_invoice`, `test_unauthorized_user_cannot_delete`)
- Satu test method, satu assertion utama
- Gunakan factory dan seeder untuk test data

## 14. Standar Branching dan Git

### 14.1 Branch Strategy

- Default branch: `master`
- Feature branch: `feature/nama-fitur` (contoh: `feature/payment-recording`)
- Bugfix branch: `fix/deskripsi-bug` (contoh: `fix/invoice-number-race-condition`)
- Hotfix: langsung ke `master` jika urgent, wajib sertakan deployment note

### 14.2 Merge

- Squash merge untuk feature branch agar history bersih
- Jangan rebase atau force-push branch yang sudah di-push kecuali ada alasan kuat dan dikomunikasikan

### 14.3 Commit Message

Commit message harus:

- singkat
- menjelaskan hasil, bukan proses berpikir
- fokus pada satu scope perubahan

Contoh:

- `Add in-app documentation module`
- `Implement MVP activity logging`
- `Show assigned users in role management`
- `Fix invoice deletion handling`

## 15. Standar Data, Environment, dan Deployment

### 15.1 Data dan Schema

Jika perubahan menyentuh data:

- sebutkan migration yang ditambahkan
- sebutkan seeder yang perlu dijalankan
- sebutkan apakah aman untuk re-run
- sebutkan dampak ke data lama

### 15.2 Environment Variables

- Setiap env variable baru wajib ditambahkan ke `.env.example` dengan nilai default yang aman.
- Jangan simpan default value yang production-specific di `.env.example`.
- Jika env var bersifat secret, gunakan placeholder seperti `your-key-here`.
- Kelompokkan env vars berdasarkan concern:
  - App (APP_NAME, APP_TIMEZONE, dll)
  - Database
  - Mail
  - Queue
  - Zabbix integration
  - Client Portal
  - Billing (BILLING_PPN_RATE, BILLING_DUE_DAYS, dll)
  - GitHub integration

### 15.3 Deployment

Jika perubahan perlu langkah production:

- tulis jelas command yang harus dijalankan
- bedakan antara:
  - wajib
  - opsional
  - hanya untuk production tertentu

## 16. Checklist Sebelum Commit

Minimal cek:

- [ ] scope perubahan jelas
- [ ] file yang tidak relevan tidak ikut ter-commit
- [ ] dokumentasi yang relevan sudah diupdate
- [ ] `CHANGELOG.md` diperbarui jika perlu
- [ ] migration/seeder/deploy note ditulis jika perlu
- [ ] syntax check / test minimum sudah dilakukan jika memungkinkan
- [ ] permission/role diperiksa jika menyentuh menu atau akses

### 16.1 Kapan Harus Commit

Commit sebaiknya dilakukan ketika salah satu kondisi ini terpenuhi:

1. satu unit pekerjaan selesai end-to-end
2. satu bug fix sudah selesai dan terverifikasi
3. satu fitur baru sudah bisa dipakai meskipun belum final 100%
4. satu perubahan dokumentasi penting sudah lengkap
5. sebelum beralih ke pekerjaan lain yang berbeda scope

Hindari:

- commit campuran untuk banyak topik yang tidak berhubungan
- commit setengah jadi tanpa konteks yang jelas
- commit file temp, cache, atau artefak lokal yang tidak relevan

### 16.2 Kapan Harus Push ke GitHub

Push sebaiknya dilakukan jika:

1. pekerjaan sudah siap dipakai atau diuji di server lain
2. perubahan sudah aman untuk branch aktif
3. dokumentasi/changelog yang relevan sudah ikut diperbarui
4. user secara eksplisit meminta commit dan push
5. perubahan diperlukan untuk deploy production, staging, atau kolaborasi tim

Jangan push jika:

- pekerjaan masih eksplorasi dan belum stabil
- perubahan belum dicek dampaknya ke modul lain
- migration/seeder/deploy note belum jelas padahal dibutuhkan
- masih ada file lokal yang tidak sengaja ikut berubah

### 16.3 Default Workflow Commit dan Push

Urutan kerja yang disarankan:

1. implement perubahan
2. verifikasi minimum
3. update dokumentasi terkait
4. update `CHANGELOG.md` jika user atau operasional akan merasakan perubahan
5. commit dengan message yang jelas
6. push ke GitHub jika perubahan sudah siap dibagikan atau dideploy

### 16.4 Persetujuan Push dan Batasan Environment

Catatan penting:

- prosedur di `AGENTS.md` tidak dapat menonaktifkan prompt persetujuan dari environment/tooling
- persetujuan untuk `git commit`, `git push`, atau command sensitif tetap mengikuti aturan sandbox/runtime
- jika environment mendukung approval rule atau trusted prefix, itu harus diatur di level tool/runtime, bukan di repo

Artinya:

- `AGENTS.md` hanya bisa menetapkan kebiasaan kerja
- keputusan akhir soal perlu atau tidaknya approval tetap ditentukan platform yang menjalankan agent

## 17. Checklist Sebelum Push ke Production

- [ ] branch sudah sinkron
- [ ] migration yang diperlukan sudah diketahui
- [ ] seeder yang diperlukan sudah diketahui
- [ ] cache clear steps sudah dicatat
- [ ] perubahan environment variable sudah dicatat
- [ ] risiko rewrite history dipahami jika force-push dilakukan

## 18. Skill Operasional

Gunakan skill berikut untuk menjaga konsistensi repo ini. Skill tersedia di dua lokasi:

- **opencode**: `C:\Users\ThinkPad\.agents\skills\`
- **Codex CLI**: `C:\Users\ThinkPad\.codex\skills\`

Cara menggunakan: panggil skill melalui skill tool saat task cocok dengan deskripsinya.

Daftar skill:

- `crm-doc-maintainer`
  - untuk memastikan `CHANGELOG.md`, `docs/`, dan deployment note ikut diperbarui
  - gunakan saat perubahan memengaruhi fitur, UI, permission, route, deployment, atau API
- `crm-release-checker`
  - untuk mengecek dampak release sebelum commit, push, atau deploy
  - gunakan saat perubahan menyentuh migration, seeder, permission, config, atau environment
- `crm-api-doc-writer`
  - untuk menjaga dokumentasi endpoint API tetap sinkron dengan route/controller
  - gunakan saat endpoint client portal berubah
- `crm-permission-auditor`
  - untuk mengecek konsistensi permission, sidebar, route, controller, dan role management
  - gunakan saat modul baru ditambah atau permission berubah
- `crm-activitylog-auditor`
  - untuk mengecek apakah aksi penting sudah tercatat di Activity Log dan field sensitif tidak ikut terlog
  - gunakan saat model atau controller baru ditambah

Skill di atas tidak menggantikan penilaian engineer, tetapi menjadi checklist operasional tambahan agar perubahan tidak lepas dari standar repo.

## 19. Standar Keamanan Kode

### 19.1 Input dan Output

- Selalu gunakan `{{ }}` di Blade untuk output yang melibatkan data user. Hindari `{!! !!}` kecuali sudah dipastikan aman dan ada komentar alasannya.
- Validasi semua input di server-side sebelum diproses, tidak boleh hanya mengandalkan validasi client-side.
- Gunakan Eloquent atau Query Builder dengan parameter binding. Jangan interpolasi variabel langsung ke raw query string.
- Whitelist tipe file yang diizinkan untuk upload. Selalu validasi mime type di server-side (jangan hanya ekstensi).
- Batasi ukuran file upload secara eksplisit di validasi dan di konfigurasi server.

### 19.2 Authentication dan Authorization

- Semua route yang memerlukan login harus dilindungi middleware `auth`.
- Semua route yang memerlukan permission tertentu harus menggunakan middleware `permission:module.action`.
- Jangan mengandalkan hanya role check (`@role()`) untuk halaman yang seharusnya permission-driven.
- Rate limiting wajib pada endpoint sensitif: login, OTP request, OTP verify, password reset.
- CSRF protection wajib pada semua form web. Pastikan `@csrf` ada di setiap form POST/PUT/DELETE.
- Authorization check harus dilakukan di controller, bukan hanya di view (jangan hanya sembunyikan tombol).

### 19.3 Data Protection dan Enkripsi

- Credential sensitif (PPPoE secret, hosting password, API key, auth code domain) wajib disimpan dengan `encrypt()`.
- Password user wajib di-hash dengan `Hash::make()`. Tidak boleh simpan plain text password.
- Field sensitif harus dikecualikan dari activity log (gunakan `$activitylogExcludeAttributes` di model).
- Data yang dikirim ke client portal hanya boleh berisi field yang diperlukan. Jangan return seluruh model.
- Jangan log nilai sensitif seperti password, token, secret, atau credential ke log file manapun.

### 19.4 Secret Management

- File `.env` tidak boleh di-commit ke repository (sudah ada di `.gitignore`, pertahankan).
- `.env.example` hanya boleh berisi placeholder, bukan nilai production (`your-api-key-here`, bukan nilai asli).
- Secret tidak boleh di-hardcode di kode sumber, config file, atau comment.
- Jika secret tidak sengaja ter-commit, rotasi secret tersebut segera dan hapus dari git history.
- Akses ke secret production harus dibatasi hanya untuk yang memerlukan (principle of least privilege).

### 19.5 API Security

- Semua endpoint client portal harus dilindungi middleware `client_portal.auth`.
- Validasi kepemilikan resource: pastikan client hanya bisa akses data miliknya sendiri (cek `client_id`).
- Token client portal memiliki TTL yang configurable via `CLIENT_PORTAL_TOKEN_TTL_DAYS`.
- Rate limiting pada OTP request sudah ada (`CLIENT_PORTAL_OTP_REQUEST_LIMIT`). Pertahankan dan dokumentasikan.
- Response error API tidak boleh mengandung stack trace atau detail internal di production.
- CORS policy harus dikonfigurasi eksplisit, tidak boleh `*` untuk production yang menggunakan auth.

### 19.6 File Upload

- File upload user harus disimpan di path yang tidak dapat diakses langsung via URL jika bersifat sensitif.
- Untuk file non-sensitif (attachment tiket, signature invoice), gunakan disk `public` dengan `storage:link`.
- Jangan percaya nama file dari user. Generate nama file baru saat menyimpan (`store()` bukan `storeAs()` dengan nama asli).
- Scan atau validasi konten file jika memungkinkan, bukan hanya ekstensi dan mime type.

### 19.7 Dependency Security

- Jalankan `composer audit` sebelum deploy ke production untuk cek CVE pada dependencies.
- Update dependency secara berkala, terutama jika ada security advisory.
- Jangan gunakan package yang sudah abandoned tanpa alternatif yang jelas.
- Lock version dependency di `composer.lock` dan commit file tersebut ke repo.

### 19.8 Mass Assignment Protection

- Selalu definisikan `$fillable` secara eksplisit di setiap model. Jangan gunakan `$guarded = []`.
- Jangan langsung pass `$request->all()` ke `create()` atau `update()` tanpa filtering terlebih dahulu.
- Gunakan `$request->validated()` (dari FormRequest) atau `$request->only([...])` untuk data yang akan disimpan.

## 20. Larangan

Jangan:

- mengubah fitur tanpa mencatat dampaknya
- menambah modul tanpa permission yang jelas
- menambah menu baru tanpa masuk ke struktur dokumentasi
- mendorong perubahan production-sensitive tanpa deployment note
- menyimpan secret nyata di repo
- hardcode value yang bisa berubah (tax rate, API URL, due days)
- membuat controller yang melebihi 500 baris tanpa mempertimbangkan extract ke service

## 20. Prioritas Dokumentasi ke Depan

Urutan yang disarankan:

1. Buat inventaris modul dan route utama.
2. Buat dokumentasi modul bisnis inti:
   - Clients
   - Subscriptions
   - Invoices
   - Tickets
   - Role Management
3. Buat dokumentasi API portal client.
4. Buat dokumentasi permission matrix.
5. Buat dokumentasi deployment dan environment.
6. Buat dokumentasi integrasi client portal dan infrastruktur.
7. Implementasi modul yang masih placeholder (Payments, Financial Reports, Work Orders).

## 21. Ringkasan Operasional

Aturan sederhananya:

- ubah kode secara hati-hati
- selalu tahu modul yang terdampak
- selalu update dokumentasi yang relevan
- selalu update changelog untuk perubahan yang user rasakan
- selalu tulis langkah deploy jika production perlu aksi manual
- gunakan skill operasional untuk validasi sebelum commit
- jangan hardcode, gunakan config
- jangan skip testing untuk logic kritis
