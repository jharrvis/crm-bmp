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

## Catatan Keamanan

- Jangan mencatat password, API key, atau credential perangkat ke Activity Log.
- Jangan mengubah `APP_KEY` tanpa rencana migrasi data terenkripsi karena credential router perlu didekripsi dengan key yang sama.
