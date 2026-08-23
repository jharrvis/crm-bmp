# Workflow Implementasi Dashboard, Notification Center, dan Operational Map

## 1. Tujuan

Dokumen ini menjadi panduan urutan kerja untuk mengimplementasikan tiga area yang saling terhubung:

1. Dashboard operasional dengan widget customizable.
2. Pusat notifikasi admin global dan actionable.
3. Operational Map dan network coverage.

Dokumen ini mengatur dependency, pekerjaan yang boleh berjalan paralel, checkpoint, testing, dokumentasi, dan commit boundary. Detail teknis fitur tetap mengikuti source plan masing-masing.

## 2. Source Plans

- [Pusat Notifikasi Admin Global dan Actionable](./pusat-notifikasi-admin.md)
- [Dashboard Operasional dan Widget Customizable](./dashboard-customizable.md)
- [Operational Map dan Network Coverage](./operational-map-network-coverage.md)
- [Integrasi Domain Registrar SRS-X](./integrasi-domain-registrar-srsx.md) untuk alur domain, registrar, approval, dan provider capability.

Jika workflow ini berbeda dengan detail source plan, gunakan urutan berikut:

1. `AGENTS.md` untuk aturan repository.
2. Workflow ini untuk dependency dan urutan implementasi.
3. Source plan untuk detail modul.
4. Kode aktual sebagai kondisi runtime.

## 3. Dependency Utama

```text
Fase 0: Kontrak dan audit kondisi aktual
                  |
       +----------+----------+
       |          |          |
 Track A      Track B      Track C
 Notification Dashboard    Operational Map MVP
 Core         Foundation
       |          |          |
       +----------+----------+
                  |
       Integrasi Dashboard–Notification–Map
                  |
       Network Coverage dan layer infrastruktur
```

Pusat notifikasi, dashboard foundation, dan Operational Map MVP dapat dikerjakan paralel setelah Fase 0 selesai. Coverage polygon dan layer network lanjutan harus menunggu data geografis infrastruktur tersedia.

## 4. Fase 0 — Kontrak Bersama dan Audit

Sebelum menulis kode:

- baca ketiga source plan;
- jalankan `graphify query` untuk topik yang terdampak;
- audit kode aktual, migration, route, controller, model, view, permission, scheduler, dan test;
- pastikan permission tidak tumpang tindih:
  - `notifications.view`, `notifications.manage`, `notifications.settings`;
  - `maps.view` untuk Operational Map MVP;
  - permission sumber seperti `clients.view`, `invoices.view`, `tickets.view`, dan `zabbix_monitors.view`;
- finalisasi kontrak `NotificationTypeRegistry`;
- finalisasi kontrak `DashboardWidgetRegistry`;
- sepakati response ringkas untuk map locations dan popup;
- catat file yang sudah ada agar tidak membuat implementasi duplikat.

### Output Fase 0

- dependency map;
- permission matrix;
- daftar migration yang dibutuhkan;
- daftar pekerjaan paralel;
- daftar file yang akan disentuh;
- keputusan audience, severity, action, source entity, dan dedupe.

## 5. Track A — Pusat Notifikasi

Fondasi notifikasi sudah tersedia di kode. Track ini berfokus pada generalisasi dan stabilisasi.

### Urutan

1. Tambahkan atau finalisasi `NotificationTypeRegistry`.
2. Pisahkan `category`, `severity`, `action_required`, `action_key`, dan `type` stabil.
3. Tambahkan `source_type`, `source_id`, dan `dedupe_key`.
4. Pisahkan lifecycle `unread`, `read`, `dismissed`, `snoozed`, dan `resolved`.
5. Buat audience resolver untuk user, role, permission, branch, division, queue, dan broadcast.
6. Pastikan CTA di-resolve server-side dan memeriksa permission sumber.
7. Migrasikan producer domain/SSL/registrar secara bertahap tanpa memutus compatibility wrapper.
8. Tambahkan producer berikut setelah core stabil:
   - payment verification;
   - invoice overdue;
   - ticket assignment/SLA;
   - system update;
   - Zabbix/server health;
   - approval workflow.

### Kriteria selesai Track A

- dedupe tidak membuat notification spam;
- read tidak otomatis berarti resolved;
- user hanya melihat notification sesuai audience dan permission;
- payload tidak mengandung secret;
- CTA yang berhasil dapat menandai notification resolved;
- test authorization, dedupe, redaction, dan lifecycle tersedia.

## 6. Track B — Dashboard Foundation

Track ini dapat berjalan paralel dengan Track A setelah kontrak registry disepakati.

### Urutan

1. Ganti route closure menjadi `DashboardController`.
2. Buat `DashboardWidgetRegistry`.
3. Buat `DashboardStatsService` dengan query real dan cache.
4. Implement widget P0:
   - Total Pelanggan;
   - Langganan per Status;
   - Outstanding Invoice;
   - Tiket Terbuka;
   - Pertumbuhan Pelanggan;
   - Aktivitas Terakhir.
5. Tambahkan empty state dan permission filtering.
6. Tambahkan `dashboard_preferences` untuk show/hide, posisi/urutan, dan ukuran kolom widget (`w`) berbasis grid 12 kolom.
7. Tetapkan `min_w`, `max_w`, dan `default_w` di `DashboardWidgetRegistry`; posisi disimpan sebagai urutan array, bukan koordinat absolut.
8. Tambahkan kontrol drag-and-drop dan preset ukuran `3/4/6/8/12`; validasi dan clamp ukuran wajib dilakukan server-side.
9. Implement default layout per role.
10. Integrasikan widget notification:
   - Notification Inbox;
   - Action Required;
   - Financial Attention;
   - Operational Health.

### Kriteria selesai Track B

- tidak ada angka demo;
- semua widget menggunakan permission server-side dan view gate;
- default layout role berjalan;
- preference user tersimpan dan tervalidasi;
- posisi/urutan dan ukuran widget tersimpan, tervalidasi, di-clamp sesuai registry, serta responsif;
- cache tidak membocorkan data lintas user/permission;
- Action Required tidak menampilkan notification resolved/dismissed.

## 7. Track C — Operational Map MVP

Track ini dapat berjalan paralel dengan Track A dan B karena memakai data koordinat yang sudah tersedia.

### Urutan

1. Tambahkan permission `maps.view` dan mapping role.
2. Buat `OperationalMapController` dan `OperationalMapService`.
3. Buat route halaman penuh dan endpoint summary/locations.
4. Tampilkan marker pelanggan dengan koordinat valid.
5. Tampilkan marker cabang menggunakan koordinat default.
6. Tambahkan popup data minimum dan link ke detail client.
7. Tambahkan filter cabang, status, layanan, dan wilayah.
8. Tambahkan mapped/unmapped summary.
9. Tambahkan clustering atau server-side grid aggregation.
10. Tambahkan widget `operational_map` ke dashboard.

### Kriteria selesai Track C

- endpoint tidak mengirim credential atau identity number;
- user tanpa `maps.view` menerima 403;
- popup hanya menampilkan client yang boleh dilihat user;
- filter dan summary konsisten;
- map bekerja pada data kosong, data kecil, dan data besar;
- attribution OpenStreetMap tetap tampil;
- pencarian Nominatim tetap melalui endpoint backend yang sudah ada.

## 8. Fase Integrasi — Dashboard, Notification, dan Map

Kerjakan setelah Track A, B, dan C memenuhi kriteria minimum.

- Dashboard `Action Required` membuka inbox dengan filter category/severity/action.
- Dashboard `Operational Health` menampilkan agregasi notification dan health summary.
- Widget `operational_map` hanya menampilkan preview; halaman penuh menangani marker dan layer.
- Notification dapat mengarahkan user ke map dengan filter terstruktur.
- Map dapat menampilkan pelanggan atau asset terdampak jika source data tersedia.
- CTA notification melakukan authorization ulang di controller sumber.
- Action berhasil mengubah notification menjadi resolved.
- Polling hanya digunakan untuk unread count; jangan reload seluruh statistik dashboard.

### Kriteria selesai Integrasi

- tidak ada query domain/SSL/invoice/ticket yang diduplikasi antara widget dan producer tanpa alasan;
- permission widget, notification, CTA, dan map konsisten;
- filter notification dan map dapat dipakai dari dashboard;
- UAT role Owner/Admin, Billing/Finance, NOC, CS, dan Sales selesai.

## 9. Fase Network Coverage Lanjutan

Jangan memulai fase ini sebelum data geografis tersedia dan tervalidasi.

Urutan:

1. Tambahkan koordinat router, POP, server, dan asset network.
2. Tambahkan endpoint atau jalur Metro Ethernet.
3. Implement layer tower/ODP setelah modul dan model tersedia.
4. Tambahkan heatmap pelanggan sebagai kepadatan marketing, bukan jaminan coverage.
5. Tambahkan GeoJSON coverage polygon dengan sumber dan tanggal data.
6. Tambahkan status `planned`, `available`, `limited`, dan `unavailable`.
7. Integrasikan outage dan pelanggan terdampak ke pusat notifikasi.

Coverage polygon tidak boleh dianggap akurat secara teknis jika belum berasal dari survey atau data desain network yang tervalidasi.

## 10. Paralelisme dan Batasan File

### Boleh paralel

- notification registry dan dashboard widget registry setelah kontrak bersama disepakati;
- DashboardStatsService dan OperationalMapService;
- UI dashboard dan UI map jika endpoint contract sudah disepakati;
- test tiap track secara terpisah;
- dokumentasi plan dan inventaris route.

### Jangan paralel tanpa koordinasi

- migration yang sama;
- `PermissionSeeder` dan role mapping yang sama;
- `routes/web.php` pada blok yang sama;
- `resources/views/layouts/header.blade.php` untuk bell dan dashboard global;
- `public/assets/js/script.js` pada fungsi global yang sama;
- model `AdminNotification` atau `User` cast yang sedang diubah track lain.

Jika dua track menyentuh file yang sama, selesaikan satu perubahan lebih dahulu atau buat subtask integrasi khusus.

## 11. Testing dan Quality Gates

Setiap track wajib menjalankan:

- PHP syntax check;
- JavaScript syntax check jika asset berubah;
- `git diff --check`;
- feature/unit test yang relevan;
- permission/authorization test;
- `graphify update` setelah perubahan kode atau dokumentasi.

Sebelum berpindah fase:

- source plan diperbarui jika keputusan berubah;
- `CHANGELOG.md` diperbarui untuk perubahan yang dirasakan user;
- migration/seeder/deployment note dicatat;
- tidak ada secret atau payload sensitif;
- manual UAT dijalankan untuk role yang terdampak.

## 12. Commit Boundary

Gunakan commit kecil dan satu scope:

1. `Add notification registry and lifecycle`
2. `Add dashboard real stats foundation`
3. `Add operational map client markers`
4. `Add customizable dashboard preferences`
5. `Integrate notification widgets`
6. `Add operational map dashboard widget`
7. `Add infrastructure map layers`

Jangan mencampur migration notification, dashboard UI, map endpoint, dan coverage polygon dalam satu commit.

## 13. Handoff Checklist AI

Sebelum mengerjakan task baru:

- baca `AGENTS.md`;
- baca workflow ini;
- baca source plan yang relevan;
- jalankan `git status --short`;
- jalankan `graphify query` untuk perubahan lintas modul;
- identifikasi track dan fase;
- sebutkan file yang akan diubah;
- jangan mengerjakan fase berikutnya sebelum kriteria fase saat ini terpenuhi.

Saat menyelesaikan task:

- laporkan track dan fase yang selesai;
- laporkan file dan migration/seeder yang berubah;
- laporkan test yang dijalankan;
- laporkan risiko dan pekerjaan lanjutan;
- jalankan `graphify update`;
- jangan commit file lokal atau artefak yang tidak relevan.
