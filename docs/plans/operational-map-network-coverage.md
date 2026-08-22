# Plan: Operational Map dan Network Coverage

## 1. Tujuan

Membangun peta operasional CRM untuk melihat sebaran pelanggan, wilayah pemasaran, lokasi cabang, dan secara bertahap jangkauan network. Peta utama tersedia sebagai halaman penuh, sedangkan dashboard hanya menampilkan widget ringkasan yang mengarah ke halaman peta.

Fitur harus memakai integrasi gratis yang sudah ada:

- Leaflet untuk interaksi peta;
- OpenStreetMap untuk tile dan attribution;
- Nominatim melalui backend `MapLocationController` untuk pencarian lokasi;
- koordinat `clients.latitude`/`clients.longitude`;
- koordinat default cabang `branches.default_latitude`/`branches.default_longitude`.

Tidak menggunakan Google Maps, API key berbayar, atau provider peta baru untuk MVP.

## 2. Kondisi Saat Ini

- Form client sudah memiliki peta Leaflet untuk memilih dan menyimpan latitude/longitude.
- Pencarian lokasi sudah melalui `MapLocationController@search` dengan Nominatim, cache, rate limit, user-agent, dan country filter Indonesia.
- Konfigurasi peta berada di `config/maps.php` dan mendukung tile URL serta attribution melalui environment.
- Cabang memiliki koordinat default yang dapat digunakan sebagai titik awal peta.
- Belum ada halaman peta operasional global.
- Belum ada clustering pelanggan, filter geografis, heatmap, layer infrastruktur, atau polygon coverage.
- Sebagian besar model infrastruktur belum memiliki koordinat geografis yang konsisten. Data tower dan ODP juga masih berupa permission placeholder.

## 3. Keputusan Scope

### MVP

- Peta pelanggan dan cabang.
- Marker pelanggan dengan popup informasi ringkas.
- Marker clustering atau agregasi berbasis grid saat zoom rendah.
- Filter cabang, status pelanggan, layanan, dan wilayah.
- Ringkasan jumlah pelanggan terpetakan dan belum terpetakan.
- Link dari marker ke halaman detail pelanggan.
- Widget dashboard “Peta Operasional” yang menampilkan ringkasan dan link ke halaman penuh.

### Di luar MVP

- Polygon coverage network yang presisi.
- Perhitungan serviceability otomatis.
- Routing jalan dan estimasi jarak berkendara.
- Integrasi provider peta komersial.
- Penyimpanan koordinat perangkat sensitif tanpa kebutuhan operasional yang jelas.

## 4. Modul dan Route

### Modul

- Nama: `Operational Map` / `Peta Operasional`.
- Group sidebar: `Infrastruktur` atau `Pelanggan`, diputuskan saat implementasi berdasarkan fokus utama halaman.
- Permission utama: `maps.view` atau `network_maps.view`. Pilih satu format dan gunakan konsisten; rekomendasi `maps.view` untuk MVP.

### Route yang disarankan

| Method | URI | Controller | Permission |
|--------|-----|------------|------------|
| GET | `/operational-map` | `OperationalMapController@index` | `maps.view` |
| GET | `/operational-map/locations` | `OperationalMapController@locations` | `maps.view` |
| GET | `/operational-map/summary` | `OperationalMapController@summary` | `maps.view` |

Endpoint `locations` harus mengembalikan data ringkas dan sudah disaring berdasarkan permission. Jangan mengirim seluruh model pelanggan ke browser.

## 5. Sumber Data dan Marker

### 5.1 Pelanggan

Sumber utama:

- `clients.id`
- `clients.name`
- `clients.client_code`
- `clients.status`
- `clients.city`
- `clients.latitude`
- `clients.longitude`
- `clients.branch_id`

Data tambahan dihitung atau dipilih secara eksplisit:

- jumlah subscription aktif;
- nama layanan utama;
- jumlah ticket terbuka;
- status portal account jika memang diperlukan.

Popup tidak boleh menampilkan identity number, password, credential layanan, atau catatan internal secara default.

### 5.2 Cabang

Sumber:

- `branches.id`
- `branches.name`
- `branches.code`
- `branches.default_latitude`
- `branches.default_longitude`

Marker cabang menjadi anchor untuk ringkasan pemasaran dan filter wilayah.

### 5.3 Infrastruktur Masa Depan

Router, POP, tower, ODP, dan jalur Metro Ethernet hanya ditampilkan jika sudah memiliki koordinat atau geometry yang tervalidasi. Jangan menggunakan alamat teks sebagai koordinat otomatis tanpa proses verifikasi.

Model generik opsional untuk fase lanjutan:

```text
map_assets
- id
- type: branch|router|pop|tower|odp|metro_ethernet
- name
- latitude nullable
- longitude nullable
- geometry json nullable
- status
- metadata json nullable
```

Gunakan GeoJSON terlebih dahulu untuk geometry. PostGIS belum diperlukan untuk MVP.

## 6. Fitur UI/UX

### 6.1 Header dan Summary Cards

Tampilkan ringkasan sebelum map:

- total pelanggan;
- pelanggan terpetakan;
- pelanggan belum memiliki koordinat;
- total cabang;
- layanan aktif;
- ticket aktif pada area yang sedang difilter.

### 6.2 Map Canvas

- Leaflet dengan tile URL dari `config/maps.php`.
- Attribution OpenStreetMap selalu terlihat.
- Default center memakai rata-rata koordinat valid atau koordinat cabang aktif pertama.
- Tombol fit bounds untuk menampilkan seluruh marker.
- Tombol lokasi saat ini hanya jika browser permission diberikan; tidak menyimpan lokasi operator.
- Legend warna marker.

### 6.3 Popup Pelanggan

Popup minimal:

- nama dan client code;
- status pelanggan;
- cabang;
- kota/wilayah;
- jumlah layanan aktif;
- tipe layanan utama;
- link `Lihat Detail Pelanggan`.

Popup harus ringkas. Detail lengkap tetap berada di halaman client.

### 6.4 Filter

Filter MVP:

- cabang;
- status pelanggan;
- status subscription;
- jenis layanan;
- provinsi/kabupaten/kota jika data tersedia;
- hanya pelanggan yang memiliki koordinat;
- hanya pelanggan tanpa koordinat.

Filter dikirim ke endpoint server dan tidak memuat seluruh database ke browser.

### 6.5 Mode Pemasaran

Fase lanjutan:

- pelanggan aktif vs prospect;
- kepadatan pelanggan per grid/wilayah;
- wilayah dengan sedikit atau tanpa pelanggan;
- perbandingan penetrasi antar cabang;
- export hasil filter ke CSV;
- heatmap pelanggan aktif.

Heatmap tidak boleh dianggap sebagai batas coverage network. Ia hanya menunjukkan kepadatan data pelanggan.

## 7. Network Coverage

### 7.1 Fase Indikator Sederhana

Sebelum polygon tersedia, tampilkan indikator yang dapat dipertanggungjawabkan:

- pelanggan dalam radius tertentu dari cabang;
- pelanggan berdasarkan cabang pemilik;
- pelanggan dengan subscription connectivity aktif;
- pelanggan yang memiliki relasi ke router atau network asset jika relasi tersebut sudah tersedia.

Radius hanya estimasi pemasaran, bukan jaminan teknis pemasangan.

### 7.2 Fase Layer Infrastruktur

Tambahkan layer:

| Layer | Kebutuhan Data | Prioritas |
|-------|----------------|-----------|
| Cabang/POP | latitude/longitude | P0 |
| Pelanggan | latitude/longitude | P0 |
| Router | koordinat lokasi | P1 |
| Metro Ethernet | endpoint atau jalur GeoJSON | P1 |
| Server hosting | lokasi terverifikasi | P1 |
| Tower | model + koordinat | P2 |
| ODP | model + koordinat | P2 |
| Coverage polygon | geometry + sumber data | P2 |

### 7.3 Coverage Polygon

Coverage polygon hanya boleh ditampilkan jika:

- sumber geometry diketahui;
- tanggal dan pemilik data tercatat;
- status coverage memiliki definisi jelas;
- polygon dapat dibedakan antara `planned`, `available`, `limited`, dan `unavailable`;
- UI menyatakan bahwa polygon adalah estimasi atau hasil survey jika belum tervalidasi teknis.

## 8. Integrasi Dashboard

Tambahkan widget `operational_map` ke `DashboardWidgetRegistry`:

```php
'operational_map' => [
    'title' => 'Peta Operasional',
    'permission' => 'maps.view',
    'route' => 'operational-map',
    'group' => 'Infrastruktur',
    'w' => 6,
],
```

Widget hanya menampilkan:

- mini map atau snapshot sederhana;
- jumlah pelanggan terpetakan;
- jumlah pelanggan tanpa koordinat;
- cabang dengan pelanggan terbanyak;
- link `Buka Peta Operasional`.

Dashboard tidak boleh memuat seluruh marker sekaligus. Halaman penuh menangani query lokasi, clustering, filter, dan layer.

## 9. Integrasi Pusat Notifikasi

Map dapat menjadi target action dari pusat notifikasi global:

- pelanggan tanpa koordinat;
- konsentrasi ticket pada area tertentu;
- router/POP offline;
- pelanggan terdampak outage;
- coverage asset dengan status bermasalah.

Notifikasi menyimpan `source_type`, `source_id`, atau filter terstruktur. Jangan menyimpan URL bebas atau data pelanggan lengkap di payload.

Contoh CTA:

```text
Lihat pelanggan terdampak di peta
```

CTA harus tetap melewati `maps.view` dan permission sumber terkait.

## 10. Arsitektur Teknis

### Backend

- `app/Http/Controllers/OperationalMapController.php`
- `app/Services/OperationalMapService.php`
- `app/Services/MapLocationQueryService.php` jika query locations perlu dipakai dashboard dan halaman penuh
- `resources/views/operational-map/index.blade.php`
- `resources/views/components/dashboard/operational-map.blade.php`

`OperationalMapService` bertanggung jawab untuk:

- query koordinat valid;
- filter server-side;
- eager load terbatas;
- summary cards;
- bounds;
- grouping atau clustering response;
- redaksi popup data.

### Frontend

- Reuse Leaflet dan konfigurasi map yang sudah ada.
- Inisialisasi map setelah container terlihat untuk menghindari ukuran canvas salah.
- Gunakan marker clustering jika jumlah marker besar; plugin harus open source dan dipin versinya.
- Jika tidak ingin menambah plugin, gunakan server-side grid aggregation pada zoom rendah.
- Jangan membuat request Nominatim per marker. Nominatim hanya untuk pencarian lokasi manual.

## 11. Performance dan Batasan Provider

- Jangan memuat seluruh pelanggan tanpa batas.
- Gunakan pagination berbasis viewport/bounds atau server-side aggregation.
- Cache summary singkat berdasarkan filter yang aman.
- Batasi jumlah marker detail pada zoom rendah.
- Hormati attribution dan kebijakan penggunaan tile OpenStreetMap.
- Jangan melakukan geocoding massal otomatis ke Nominatim.
- Simpan koordinat yang sudah diverifikasi agar tidak melakukan pencarian berulang.
- Tambahkan empty state untuk data tanpa koordinat.

## 12. Keamanan dan Privasi

- Halaman dan endpoint memakai `auth`, `verified`, `ip.restrict`, dan `maps.view`.
- Popup hanya berisi field yang diperlukan operasional.
- Nomor identitas, credential, password, token, dan secret tidak pernah dikirim ke endpoint map.
- Pastikan client hanya bisa dilihat oleh staff yang memiliki `clients.view`.
- Jika nanti ada data outage atau network sensitive, tambahkan permission terpisah seperti `network_maps.view` atau filter layer berdasarkan permission sumber.
- Aktivitas perubahan koordinat pelanggan tetap tercatat melalui activity log model Client.

## 13. Tahapan Implementasi

### Fase 1 — Peta Pelanggan dan Cabang

- Tambah permission `maps.view` dan mapping role.
- Buat controller/service/route halaman peta.
- Endpoint locations hanya mengirim pelanggan dengan koordinat valid dan data popup minimum.
- Tambah marker pelanggan dan cabang.
- Tambah filter dasar, fit bounds, legend, popup, dan link detail.
- Tambah summary jumlah mapped/unmapped.

### Fase 2 — Dashboard dan Marketing View

- Tambah widget `operational_map`.
- Tambah mode pelanggan aktif/prospect.
- Tambah agregasi kepadatan per wilayah/grid.
- Tambah export CSV hasil filter.
- Tambah heatmap sebagai visualisasi kepadatan, bukan coverage guarantee.

### Fase 3 — Infrastruktur Network

- Tambah koordinat router/POP/server.
- Tambah layer Metro Ethernet endpoint/jalur.
- Tambah model atau integrasi tower/ODP setelah modulnya tersedia.
- Tambah filter status asset dan relasi pelanggan terdampak.

### Fase 4 — Coverage Polygon dan Notifikasi

- Tambah GeoJSON coverage dengan metadata sumber dan tanggal.
- Tambah serviceability indicator yang dibedakan dari coverage estimasi.
- Integrasikan notifikasi outage, network asset failure, dan pelanggan terdampak.

## 14. Testing dan UAT

### Feature Test

- user tanpa `maps.view` menerima 403;
- endpoint tidak mengembalikan client tanpa koordinat pada mode mapped;
- filter branch/status/service membatasi hasil dengan benar;
- response tidak mengandung identity number atau credential;
- popup/link hanya menunjuk ke client yang boleh dilihat user;
- summary mapped/unmapped konsisten dengan query locations;
- notifikasi CTA map tetap memeriksa permission.

### Manual UAT

- buka halaman map pada data kosong;
- buka pada satu pelanggan dan banyak pelanggan;
- uji dark mode dan resize container;
- uji marker click dan link ke detail pelanggan;
- uji filter antar cabang;
- uji rate limit pencarian lokasi tetap berlaku;
- uji tile attribution tetap terlihat;
- uji browser lambat dan koneksi Nominatim gagal.

## 15. Deployment dan Dokumentasi

- MVP tidak membutuhkan API key baru.
- Jika permission baru ditambahkan, jalankan seeder permission dan reset permission cache sesuai prosedur deployment.
- Jika menambah tabel `map_assets` atau geometry, tambahkan migration dan deployment note.
- `MAP_NOMINATIM_USER_AGENT` tetap wajib dikonfigurasi untuk pencarian lokasi operasional.
- Update `docs/modules/` untuk modul Operational Map setelah implementasi.
- Update `docs/plans/dashboard-customizable.md` jika kontrak widget berubah.
- Jalankan `graphify update` setelah perubahan kode atau dokumentasi.

## 16. Keputusan Terbuka

1. Permission cukup `maps.view`, atau perlu dipisah menjadi `maps.view` dan `network_maps.view` saat layer infrastruktur tersedia?
2. Untuk clustering, gunakan plugin Leaflet open source atau server-side grid aggregation?
3. Apakah pelanggan tanpa koordinat boleh ditampilkan sebagai daftar samping, bukan marker?
4. Sumber resmi coverage polygon berasal dari survey, desain network, atau input manual operator?
5. Apakah heatmap marketing boleh dilihat semua role dengan `clients.view`, atau dibatasi Sales/Owner/Admin?

## 17. Referensi

- `config/maps.php`
- `app/Http/Controllers/MapLocationController.php`
- `routes/web.php`
- `resources/views/clients/index.blade.php`
- `app/Models/Client.php`
- `app/Models/Branch.php`
- `docs/modules/clients.md`
- `docs/deployment.md`
- `docs/plans/dashboard-customizable.md`
- `docs/plans/pusat-notifikasi-admin.md`
