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

Contoh modul yang harus dipetakan:

- Dashboard
- Global Search
- Cabang
- Divisi
- Karyawan
- Manajemen Role
- Clients / Pelanggan
- Client Contacts
- Subscriptions / Langganan
- Services
- Packages
- Invoices
- Payments / Laporan Keuangan
- Tickets
- Infrastructure
  - Routers
  - Hosting Servers
  - Vendors
  - Metro Ethernet
  - Zabbix Monitors
- System Updates
- Activity Log
- Settings
- Client Portal

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

## 11. Standar Data dan Deployment

Jika perubahan menyentuh data:

- sebutkan migration yang ditambahkan
- sebutkan seeder yang perlu dijalankan
- sebutkan apakah aman untuk re-run
- sebutkan dampak ke data lama

Jika perubahan perlu langkah production:

- tulis jelas command yang harus dijalankan
- bedakan antara:
  - wajib
  - opsional
  - hanya untuk production tertentu

## 12. Checklist Sebelum Commit

Minimal cek:

- [ ] scope perubahan jelas
- [ ] file yang tidak relevan tidak ikut ter-commit
- [ ] dokumentasi yang relevan sudah diupdate
- [ ] `CHANGELOG.md` diperbarui jika perlu
- [ ] migration/seeder/deploy note ditulis jika perlu
- [ ] syntax check / test minimum sudah dilakukan jika memungkinkan
- [ ] permission/role diperiksa jika menyentuh menu atau akses

## 12.0 Skill Operasional yang Disarankan

Jika environment agent mendukung skill lokal, gunakan skill berikut untuk menjaga konsistensi repo ini:

- `crm-doc-maintainer`
  - untuk memastikan `CHANGELOG.md`, `docs/`, dan deployment note ikut diperbarui
- `crm-release-checker`
  - untuk mengecek dampak release sebelum commit, push, atau deploy
- `crm-api-doc-writer`
  - untuk menjaga dokumentasi endpoint API tetap sinkron dengan route/controller
- `crm-permission-auditor`
  - untuk mengecek konsistensi permission, sidebar, route, controller, dan role management
- `crm-activitylog-auditor`
  - untuk mengecek apakah aksi penting sudah tercatat di Activity Log dan field sensitif tidak ikut terlog

Skill di atas tidak menggantikan penilaian engineer, tetapi menjadi checklist operasional tambahan agar perubahan tidak lepas dari standar repo.

## 12.1 Kapan Harus Commit

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

## 12.2 Standar Isi Commit

Commit message harus:

- singkat
- menjelaskan hasil, bukan proses berpikir
- fokus pada satu scope perubahan

Contoh:

- `Add in-app documentation module`
- `Implement MVP activity logging`
- `Show assigned users in role management`
- `Fix invoice deletion handling`

## 12.3 Kapan Harus Push ke GitHub

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

## 12.4 Default Workflow Commit dan Push

Urutan kerja yang disarankan:

1. implement perubahan
2. verifikasi minimum
3. update dokumentasi terkait
4. update `CHANGELOG.md` jika user atau operasional akan merasakan perubahan
5. commit dengan message yang jelas
6. push ke GitHub jika perubahan sudah siap dibagikan atau dideploy

## 12.5 Persetujuan Push dan Batasan Environment

Catatan penting:

- prosedur di `AGENTS.md` tidak dapat menonaktifkan prompt persetujuan dari environment/tooling
- persetujuan untuk `git commit`, `git push`, atau command sensitif tetap mengikuti aturan sandbox/runtime
- jika environment mendukung approval rule atau trusted prefix, itu harus diatur di level tool/runtime, bukan di repo

Artinya:

- `AGENTS.md` hanya bisa menetapkan kebiasaan kerja
- keputusan akhir soal perlu atau tidaknya approval tetap ditentukan platform yang menjalankan agent

## 13. Checklist Sebelum Push ke Production

- [ ] branch sudah sinkron
- [ ] migration yang diperlukan sudah diketahui
- [ ] seeder yang diperlukan sudah diketahui
- [ ] cache clear steps sudah dicatat
- [ ] perubahan environment variable sudah dicatat
- [ ] risiko rewrite history dipahami jika force-push dilakukan

## 14. Larangan

Jangan:

- mengubah fitur tanpa mencatat dampaknya
- menambah modul tanpa permission yang jelas
- menambah menu baru tanpa masuk ke struktur dokumentasi
- mendorong perubahan production-sensitive tanpa deployment note
- menyimpan secret nyata di repo

## 15. Prioritas Dokumentasi ke Depan

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

## 16. Ringkasan Operasional

Aturan sederhananya:

- ubah kode secara hati-hati
- selalu tahu modul yang terdampak
- selalu update dokumentasi yang relevan
- selalu update changelog untuk perubahan yang user rasakan
- selalu tulis langkah deploy jika production perlu aksi manual
