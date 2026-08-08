# Subscriptions / Langganan

## Tujuan

Mengelola langganan layanan pelanggan ISP. Mendukung empat jenis layanan: connectivity (internet), hosting, domain, dan mail hosting. Setiap subscription terkait dengan satu client dan satu paket layanan, dengan pricing model yang fleksibel.

## Entitas Terkait

| Model | File | Keterangan |
|-------|------|------------|
| `Subscription` | `app/Models/Subscription.php` | Entitas utama langganan |
| `SubscriptionConnectivity` | `app/Models/SubscriptionConnectivity.php` | Detail teknis koneksi internet |
| `SubscriptionHosting` | `app/Models/SubscriptionHosting.php` | Detail server hosting |
| `SubscriptionDomain` | `app/Models/SubscriptionDomain.php` | Detail domain registrasi |
| `SubscriptionMailHosting` | `app/Models/SubscriptionMailHosting.php` | Detail layanan mail hosting |
| `Mailbox` | `app/Models/Mailbox.php` | Akun mailbox pada layanan mail hosting |
| `SubscriptionTopology` | `app/Models/SubscriptionTopology.php` | Topologi jaringan per subscription |
| `SubscriptionTopologyHistory` | `app/Models/SubscriptionTopologyHistory.php` | Riwayat perubahan topologi |
| `Service` | `app/Models/Service.php` | Jenis layanan (connectivity/hosting/domain) |
| `Package` | `app/Models/Package.php` | Paket layanan dengan harga |

### Relasi Subscription

- `client()` → belongsTo `Client`
- `package()` → belongsTo `Package`
- `connectivity()` → hasOne `SubscriptionConnectivity`
- `hosting()` → hasOne `SubscriptionHosting`
- `domain()` → hasOne `SubscriptionDomain`
- `mailHosting()` → hasOne `SubscriptionMailHosting`
- `mailboxList()` → hasManyThrough `Mailbox`
- `topology()` → hasOne `SubscriptionTopology`
- `tickets()` → hasMany `Ticket`

### Field Subscription

`client_id`, `package_id`, `subscription_code`, `status`, `installed_at`, `billing_cycle_day`, `next_billing_date`, `terminated_at`, `termination_reason`, `price_at_subscription`, `custom_price`, `billing_period_months`, `uses_ppn`, `ppn_amount`, `uses_pph23`, `pph23_amount`, `discount_percent`, `discount_notes`, `notes`

### Pricing Model

| Komponen | Kalkulasi |
|----------|-----------|
| `base_price` | `custom_price ?? (package.price * billing_period_months)` |
| `ppn_amount` | `base_price * 0.11` (11% PPN, jika `uses_ppn = true`) |
| `pph23_amount` | `base_price * 0.02` (2% PPh23, jika `uses_pph23 = true`) |
| `effective_price` | `base_price + ppn_amount - pph23_amount` |

PPN dan PPh23 rate saat ini hardcoded. Lihat plan audit tagihan untuk rencana migrasi ke `config/billing.php`.

## Route Utama

| Method | URI | Controller | Permission |
|--------|-----|------------|------------|
| GET | `/subscriptions` | `SubscriptionController@index` | `subscriptions.view` |
| POST | `/subscriptions` | `SubscriptionController@store` | `subscriptions.create` |
| GET | `/subscriptions/{subscription}` | `SubscriptionController@show` | `subscriptions.view` |
| PUT/PATCH | `/subscriptions/{subscription}` | `SubscriptionController@update` | `subscriptions.update` |
| DELETE | `/subscriptions/{subscription}` | `SubscriptionController@destroy` | `subscriptions.delete` |

### Topology Editor (nested)

| Method | URI | Route Name |
|--------|-----|------------|
| GET | `/subscriptions/{subscription}/topology` | `subscriptions.topology.show` |
| POST | `/subscriptions/{subscription}/topology` | `subscriptions.topology.store` |
| GET | `/subscriptions/{subscription}/topology/history` | `subscriptions.topology.history` |
| POST | `/subscriptions/{subscription}/topology/restore/{historyId}` | `subscriptions.topology.restore` |
| POST | `/subscriptions/{subscription}/topology/save-template` | `subscriptions.topology.save-template` |

### Client Portal API

| Method | URI | Keterangan |
|--------|-----|------------|
| GET | `/api/client-portal/subscriptions` | Daftar langganan client |
| GET | `/api/client-portal/subscriptions/{id}` | Detail langganan |
| GET | `/api/client-portal/subscriptions/{id}/usage` | Data usage (Zabbix) |
| GET | `/api/client-portal/subscriptions/{id}/usage-chart` | Chart data usage |

## Permission

| Permission | Deskripsi |
|------------|-----------|
| `subscriptions.view` | Melihat daftar dan detail langganan |
| `subscriptions.create` | Membuat langganan baru |
| `subscriptions.update` | Mengubah data langganan |
| `subscriptions.delete` | Menghapus langganan |
| `subscriptions.suspend` | Suspend langganan |
| `subscriptions.activate` | Mengaktifkan kembali langganan |

### Default Role Mapping

| Role | view | create | update | delete | suspend | activate |
|------|------|--------|--------|--------|---------|----------|
| Owner | v | v | v | v | v | v |
| Admin | v | v | v | v | v | v |
| Employee | v | v | v | - | - | - |
| Billing | v | - | - | - | - | - |
| NOC | v | - | v | - | v | v |
| CS | v | v | v | - | - | - |
| Sales | v | v | - | - | - | - |
| Finance | v | - | - | - | - | - |

## Alur Bisnis

### Pembuatan Langganan

1. Staff memilih client dan paket layanan.
2. Sistem mendeteksi tipe layanan dari paket (connectivity/hosting/domain/mail).
3. Form menampilkan field teknis sesuai tipe:
   - **Connectivity**: router, IP, PPPoE, ONT S/N, Zabbix monitoring, Metro Ethernet
   - **Hosting**: server, domain, username, password (terenkripsi), disk quota
   - **Domain**: nama domain, registrar, tanggal registrasi/berakhir, auth code (terenkripsi), dan catatan domain
   - **Mail**: mail server Zimbra, domain, dan kontak admin opsional
4. Sistem generate `subscription_code` format: `{client_code}-{SERVICE_CODE}{NN}` (contoh: `126001-INT01`).
5. `billing_cycle_day` diambil dari tanggal instalasi.
6. `next_billing_date` = tanggal instalasi + 1 bulan.
7. PPN/PPh23 dihitung otomatis jika toggle aktif.

### Perubahan Paket

- Paket dapat diubah dari form edit selama masih berada pada jenis layanan yang sama, misalnya paket internet ke paket internet lain.
- Saat paket berubah, sistem memperbarui harga paket yang terkunci, periode billing, pajak, `billing_cycle_day`, dan `next_billing_date` dari tanggal pemasangan.
- Prorata upgrade/downgrade memakai harga dan periode paket lama sebelum perubahan.
- Perpindahan antar jenis layanan (connectivity, hosting, domain, mail) tidak diizinkan dari form edit karena perlu migrasi data teknis dan, untuk hosting, koordinasi akun HestiaCP.

### Connectivity-specific

- Integrasi dengan Zabbix untuk monitoring: host, group, interfaces/graphs.
- Metro Ethernet bisa link ke existing atau buat baru saat store.
- PPPoE secret dan password tersimpan terenkripsi (`encrypt()`).

### Hosting-specific

- Integrasi dengan HestiaCP API (`HestiaCPService`):
  - `store`: auto-create user dan web domain di server hosting.
  - `update`: auto-suspend/unsuspend/change password sesuai status.

### Domain-specific

- Detail domain disimpan pada `domain`; tidak lagi field panel hosting.
- Auth code bersifat opsional dan disimpan terenkripsi. Saat edit, auth code lama tidak ditampilkan dan hanya berubah bila nilai baru diisi.

### Mail-specific

- Tipe layanan `mail` memakai `SubscriptionMailHosting` + `Mailbox`.
- Domain mail diprovisikan melalui queue setelah subscription tersimpan.
- Mail server dipilih dari daftar `HostingServer` tipe `zimbra`.
- Pembuatan/suspend/activate/delete mailbox diantrekan ke Zimbra SOAP lewat `MailboxController`.

### Status Langganan

| Status | Deskripsi |
|--------|-----------|
| `pending` | Belum aktif, menunggu instalasi |
| `active` | Aktif dan berlangganan |
| `suspended` | Ditangguhkan (hosting: user di-suspend di HestiaCP) |
| `terminated` | Dihentikan permanen |

## Integrasi Modul Lain

| Modul | Relasi |
|-------|--------|
| Client | Setiap subscription milik satu client |
| Package / Service | Subscription merujuk ke paket dan layanan |
| Invoice | Invoice item merujuk ke subscription untuk billing |
| Ticket | Tiket bisa dikaitkan ke subscription tertentu |
| Router | Connectivity subscription bisa terkait router |
| Hosting Server | Hosting subscription terkait server HestiaCP |
| Metro Ethernet | Connectivity bisa terkait link metro ethernet |
| Zabbix | Monitoring koneksi via Zabbix API |
| Topology | Topologi jaringan visual per subscription |
| Hosting Server (zimbra) | Mail hosting terkait server Zimbra via `SubscriptionMailHosting` |

## Seeder / Migration Terkait

| File | Keterangan |
|------|------------|
| `create_subscriptions_table` | Tabel utama langganan |
| `create_subscription_mail_hostings_table` | Detail mail hosting per subscription |
| `create_subscription_connectivities_table` | Detail teknis koneksi |
| `create_subscription_hostings_table` | Detail hosting |
| `create_subscription_domains_table` | Detail domain |
| `create_subscription_topologies_table` | Topologi jaringan |
| `add_flexible_pricing_to_subscriptions_table` | Custom price, billing_period_months, discount |
| `add_ppn_fields_to_subscriptions_table` | PPN toggle dan amount |
| `add_pph23_fields_to_subscriptions_table` | PPh23 toggle dan amount |

## Known Issues / Catatan

- `billing_cycle_day` di-set saat create tapi **tidak digunakan** oleh proses generate invoice bulanan. Invoice generate saat ini menggunakan tanggal manual.
- `next_billing_date` diperbarui saat tanggal pemasangan diubah, tetapi belum di-update otomatis setelah invoice bulanan dibuat. Lihat plan audit tagihan untuk perbaikan.
- PPN (11%) dan PPh23 (2%) rate hardcoded di `Subscription::calculatePpnAmount()` dan `calculatePph23Amount()`.
- `suspend` dan `activate` permission ada di seeder tapi controller menggunakan `update` permission untuk semua perubahan status.
- Password hosting dan PPPoE secret tersimpan terenkripsi via Laravel `encrypt()`.
- Activity log aktif pada model `Subscription` (entity name: `langganan`).
