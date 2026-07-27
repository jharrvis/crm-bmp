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

Halaman detail Router menampilkan host, port API, cabang, status, deskripsi, username, dan password router. Password disimpan dengan encrypted cast Laravel, dikecualikan dari Activity Log, dan tersedia melalui kontrol tampilkan/sembunyikan atau salin bagi pengguna yang mempunyai `routers.view`.

Daftar langganan yang terkait ditentukan dari relasi `subscription_connectivities.router_id`, termasuk pelanggan, kode langganan, paket, alamat IP, dan PPPoE user bila tersedia.

### Integrasi

- Global Search mengarahkan hasil Router ke halaman detail router.
- Subscription Connectivity memilih Router sebagai gateway/langganan terkait.
- Cabang menjadi penanda lokasi atau kepemilikan operasional router.

## Catatan Keamanan

- Jangan mencatat password, API key, atau credential perangkat ke Activity Log.
- Jangan mengubah `APP_KEY` tanpa rencana migrasi data terenkripsi karena credential router perlu didekripsi dengan key yang sama.
