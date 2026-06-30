# Plan: Modul Tagihan (Invoice/Billing) v3

Tanggal: 30 Juni 2026
Update dari: audit-modul-tagihan-v2.md

---

## Perubahan dari Plan v2

1. **Prorata**: Ditambahkan fitur penghitungan prorata untuk 3 skenario: register baru mid-cycle, upgrade/downgrade paket, suspend/terminate mid-cycle. Metode: per hari aktual bulan. Ditampilkan sebagai line item terpisah di invoice. Configurable on/off dari Pengaturan Sistem.

---

## Arsitektur Pengaturan Billing

### Tabel `system_settings`

```
id, group (string), key (string unique), value (text nullable), 
type (enum: string/integer/float/boolean/json), 
description (string nullable), created_at, updated_at
```

### Default Settings (Billing Group)

| Key | Default | Type | Deskripsi |
|-----|---------|------|-----------|
| `billing.ppn_rate` | `11` | float | Tarif PPN (%) |
| `billing.pph23_rate` | `2` | float | Tarif PPh23 (%) |
| `billing.default_due_days` | `7` | integer | Hari jatuh tempo dari tanggal generate |
| `billing.auto_generate_day` | `1` | integer | Tanggal auto-generate invoice setiap bulan (1-28) |
| `billing.auto_generate_enabled` | `false` | boolean | Toggle auto-generate aktif/nonaktif |
| `billing.proration_enabled` | `true` | boolean | Toggle penghitungan prorata aktif/nonaktif |
| `billing.reminder_days_before` | `[7,3,1]` | json | Hari reminder sebelum jatuh tempo |
| `billing.reminder_days_after` | `[1,7,14]` | json | Hari reminder setelah overdue |
| `billing.reminder_channel` | `email` | string | Channel reminder: email, whatsapp, both |

### Model `SystemSetting`

```php
class SystemSetting extends Model {
    protected $fillable = ['group', 'key', 'value', 'type', 'description'];
    
    public static function get(string $key, mixed $default = null): mixed
    public static function set(string $key, mixed $value): void
    public static function getGroup(string $group): Collection
}
```

### Helper Global

```php
function setting(string $key, mixed $default = null): mixed {
    return SystemSetting::get($key, $default);
}
```

### Penggunaan

Semua tempat yang sebelumnya hardcode `0.11`, `0.02`, `7` diganti:
- `setting('billing.ppn_rate') / 100` → menggantikan `0.11`
- `setting('billing.pph23_rate') / 100` → menggantikan `0.02`
- `setting('billing.default_due_days')` → menggantikan `7`

---

## Fitur Prorata

### Konsep

Prorata menghitung biaya layanan secara proporsional berdasarkan jumlah hari aktif dalam satu bulan terhadap jumlah hari aktual bulan tersebut.

**Formula:**
```
prorata_amount = (base_price / jumlah_hari_dalam_bulan) × jumlah_hari_aktif
```

**Contoh**: Harga bulanan Rp 1.000.000, register tanggal 15 Juli (bulan 31 hari):
```
prorata = (1.000.000 / 31) × 17 hari (15-31 Juli) = Rp 548.387
```

### Skenario yang Didukung

#### 1. Register Baru Mid-Cycle

**Kapan**: Subscription baru dibuat (`installed_at`) di luar tanggal generate bulanan.

**Alur**:
1. Staff buat subscription baru pada tanggal 15.
2. Jika `billing.proration_enabled = true`:
   - Sistem otomatis buat invoice prorata dari tanggal install sampai akhir siklus billing (akhir bulan atau tanggal generate bulan berikutnya).
   - Item invoice: "Prorata Langganan {paket} ({tanggal_mulai} - {tanggal_akhir_siklus}, {N} hari)"
3. Jika `billing.proration_enabled = false`:
   - Invoice full-month dibuat seperti biasa.

**Trigger**: `SubscriptionController::store()` — setelah subscription berhasil dibuat.

#### 2. Upgrade/Downgrade Paket

**Kapan**: Subscription diubah ke paket yang berbeda (harga berubah) di tengah siklus.

**Alur**:
1. Staff update subscription → paket berubah dari A (Rp 500.000) ke B (Rp 1.000.000) pada tanggal 10.
2. Jika `billing.proration_enabled = true`:
   - Hitung kredit sisa hari paket lama: `(harga_lama / hari_bulan) × sisa_hari`
   - Hitung biaya sisa hari paket baru: `(harga_baru / hari_bulan) × sisa_hari`
   - Selisih = biaya baru - kredit lama
   - Buat invoice dengan 2 line item:
     - "Kredit Langganan {paket_lama} ({tanggal_upgrade} - {akhir_siklus}, {N} hari)" → amount negatif
     - "Prorata Langganan {paket_baru} ({tanggal_upgrade} - {akhir_siklus}, {N} hari)" → amount positif
3. Jika selisih <= 0 (downgrade): bisa jadi kredit memo atau diabaikan (configurable nanti).

**Trigger**: `SubscriptionController::update()` — jika `package_id` atau `custom_price` berubah.

#### 3. Suspend/Terminate Mid-Cycle

**Kapan**: Subscription di-suspend atau di-terminate di tengah siklus, dan invoice bulan ini sudah terbayar.

**Alur**:
1. Staff suspend/terminate subscription pada tanggal 20.
2. Jika `billing.proration_enabled = true` DAN invoice bulan ini sudah `paid`:
   - Hitung kredit sisa hari: `(base_price / hari_bulan) × sisa_hari_tidak_terpakai`
   - Buat credit memo / invoice kredit:
     - "Kredit Suspend Langganan {paket} ({tanggal_suspend} - {akhir_siklus}, {N} hari)" → amount negatif
   - Credit memo bisa diaplikasikan ke invoice berikutnya (jika reactive) atau sebagai catatan.
3. Jika invoice belum bayar: pertimbangkan adjust invoice existing (kurangi total).

**Trigger**: `SubscriptionController::update()` — jika `status` berubah ke `suspended` atau `terminated`.

### Service Class: `ProrataCalculationService`

```php
class ProrataCalculationService
{
    public function calculateNewSubscription(Subscription $sub): ?array
    public function calculateUpgradeDowngrade(Subscription $sub, float $oldBasePrice, float $newBasePrice, Carbon $changeDate): ?array
    public function calculateSuspendTerminate(Subscription $sub, Carbon $terminationDate): ?array

    protected function dailyRate(float $monthlyPrice, Carbon $date): float
    {
        return $monthlyPrice / $date->daysInMonth;
    }

    protected function remainingDays(Carbon $fromDate): int
    {
        return $fromDate->daysInMonth - $fromDate->day + 1;
    }
}
```

**Return format** (array of invoice items):
```php
[
    [
        'description' => 'Prorata Langganan Internet 10Mbps (15 Jul - 31 Jul, 17 hari)',
        'amount' => 548387.10,
        'qty' => 1,
        'total' => 548387.10,
        'subscription_id' => 123,
        'is_prorated' => true,
    ],
]
```

### Pengaruh ke Invoice

**InvoiceItem** — tambah field:

```
is_prorated (boolean, default false)
proration_start_date (date, nullable)
proration_end_date (date, nullable)
proration_days (integer, nullable)
```

Ini memungkinkan:
- Invoice menampilkan "17 dari 31 hari" di detail item
- Printable invoice menunjukkan periode prorata secara transparan
- Audit trail jelas berapa hari dan periode yang dihitung

### Pengaruh ke Generate Bulanan

`GenerateMonthlyInvoices` job:
- Jika subscription `installed_at` di bulan ini DAN sudah ada invoice prorata → skip (jangan double-charge)
- Bulan berikutnya → generate full-month seperti biasa
- Dedup check diperluas: cek invoice prorata DAN invoice bulanan

### UI Pengaturan

Di halaman Pengaturan Sistem, section Billing:
```
[x] Aktifkan Penghitungan Prorata
    Jika aktif, sistem akan menghitung biaya proporsional saat:
    - Pelanggan baru mendaftar di tengah siklus billing
    - Pelanggan upgrade/downgrade paket
    - Pelanggan suspend/terminate di tengah siklus
```

### UI Invoice

Di printable invoice (`invoices/show.blade.php`):
- Item prorata ditampilkan dengan keterangan periode: "(15 Jul - 31 Jul 2026, 17 hari)"
- Badge "PRORATA" opsional pada item untuk membedakan dari item reguler

Di manual invoice form (`invoices/create.blade.php`):
- Staff bisa menambahkan item prorata manual jika diperlukan
- Atau menggunakan tombol "Hitung Prorata" yang memanggil `ProrataCalculationService`

---

## Phase 1: Fondasi Billing

### 1.1 SystemSetting (Model + Migration + Seeder + UI)

**Migration `create_system_settings_table`:**
```
id, group, key (unique), value (text nullable), type, description, timestamps
```

**Seeder `SystemSettingSeeder`:**
- Seed default billing settings (termasuk `billing.proration_enabled`)
- Safe re-run: gunakan `updateOrCreate`

**Controller `SystemSettingController`:**
- Refactor dari stub menjadi full CRUD
- `index()`: tampilkan semua settings grouped
- `update()`: update value per key atau bulk per group
- Permission: `settings.view`, `settings.update`

**View `settings/index.blade.php`:**
- Tab/section per group (awalnya hanya `billing`)
- Form input sesuai type: number untuk float/integer, toggle untuk boolean, textarea untuk json
- Validasi: PPN/PPh23 range 0-100, due days min 1, generate day 1-28, reminder array valid

### 1.2 Refactor Hardcoded Tax Rates

**Files terdampak:**

| File | Baris | Perubahan |
|------|-------|-----------|
| `app/Models/Subscription.php:108-116` | `calculatePpnAmount`, `calculatePph23Amount` | Ganti `0.11` → `setting('billing.ppn_rate') / 100`, `0.02` → `setting('billing.pph23_rate') / 100` |
| `app/Http/Controllers/InvoiceController.php:250-251` | `buildManualInvoicePayload` | Ganti `11.0` dan `0.11` → `setting('billing.ppn_rate')` |
| `app/Http/Controllers/InvoiceController.php:409` | `generate()` due date | Ganti `addDays(7)` → `addDays(setting('billing.default_due_days'))` |
| `resources/views/invoices/create.blade.php:828` | JS preview | Ganti `0.11` → dynamic dari server-rendered `@json(setting('billing.ppn_rate') / 100)` |
| `resources/views/subscriptions/show.blade.php:1975-1976` | JS preview | Idem |
| `resources/views/subscriptions/index.blade.php:913-914` | JS preview | Idem |

### 1.3 Fix Invoice Generate Inconsistency

**Masalah**: `generate()` menyimpan `effective_price` (with tax) sebagai `total_amount` tapi `base_price` (without tax) sebagai item amount. Untuk subscription dengan PPN, item total ≠ invoice total.

**Solusi**: Saat generate, isi juga field manual invoice:
```php
$invoice = Invoice::create([
    'invoice_date' => now(),
    'due_date' => now()->addDays(setting('billing.default_due_days')),
    'subtotal_amount' => $sub->base_price,
    'uses_tax' => $sub->uses_ppn,
    'tax_rate' => $sub->uses_ppn ? setting('billing.ppn_rate') : null,
    'tax_amount' => $sub->uses_ppn ? $sub->ppn_amount : 0,
    'discount_amount' => 0,
    'total_amount' => $sub->effective_price,
    'status' => 'unpaid',
]);
```

### 1.4 Prorata Implementation

**Service `ProrataCalculationService`:**
- `calculateNewSubscription()` — prorata register baru
- `calculateUpgradeDowngrade()` — prorata upgrade/downgrade
- `calculateSuspendTerminate()` — kredit suspend/terminate
- `dailyRate()` — harga per hari berdasarkan hari aktual bulan
- `remainingDays()` — sisa hari dalam siklus

**Migration `add_proration_fields_to_invoice_items`:**
```
is_prorated (boolean default false)
proration_start_date (date nullable)
proration_end_date (date nullable)
proration_days (integer nullable)
```

**Integrasi ke controller:**
- `SubscriptionController::store()` — panggil `calculateNewSubscription()`, buat invoice prorata jika enabled
- `SubscriptionController::update()` — panggil `calculateUpgradeDowngrade()` jika paket/harga berubah, `calculateSuspendTerminate()` jika status berubah ke suspended/terminated
- `InvoiceController::generate()` — skip subscription yang sudah punya invoice prorata bulan ini

### 1.5 Payment Model & Recording

**Migration `create_payments_table`:**
```
id, invoice_id (FK), amount (decimal 15,2), 
payment_method (enum: transfer/cash/qris/other),
payment_date (date), reference_number (nullable), 
proof_path (nullable), notes (nullable), 
status (enum: pending/verified/rejected),
verified_by (FK users nullable), verified_at (datetime nullable),
rejected_reason (nullable), created_at, updated_at
```

**Migration `add_partially_paid_to_invoices`:**
- Alter invoice status ENUM: tambah `partially_paid`

**Model `Payment`:**
- Relasi: `belongsTo(Invoice)`, `belongsTo(User, 'verified_by')`
- Trait: `LogsModelActivity`
- Method: `updateInvoiceStatus()` — cek total payments vs invoice total:
  - >= total → `paid` + set `paid_at`
  - > 0 tapi < total → `partially_paid`
  - 0 → `unpaid` / `overdue` (tergantung due_date)

**Controller `PaymentController`:**
- `index()` — daftar pembayaran (filter status, date range, client)
- `store()` — catat pembayaran baru, auto-update invoice status
- `verify()` — verifikasi oleh Finance/Owner/Admin
- `reject()` — tolak dengan alasan
- Permission: `payments.view/create/update/delete/verify` (sudah ada di seeder)

**Views:**
- `payments/index.blade.php` — daftar pembayaran
- `payments/create.blade.php` — form input pembayaran
- Tab pembayaran di `invoices/show.blade.php`

### 1.6 Auto-generate Invoice Bulanan

**Job `GenerateMonthlyInvoices`:**
- Query active subscriptions
- Tanggal generate: `setting('billing.auto_generate_day')` — configurable dari UI
- Dedup: cek apakah invoice bulan ini sudah ada (termasuk invoice prorata)
- Fix pricing: isi semua field (subtotal, tax, discount, total) dengan benar
- Update `next_billing_date` pada subscription setelah generate
- Log hasil ke activity log

**Schedule (routes/console.php):**
```php
Schedule::job(new GenerateMonthlyInvoices)->dailyAt('00:05')
    ->when(function () {
        return setting('billing.auto_generate_enabled') 
            && now()->day == setting('billing.auto_generate_day');
    });
```

### 1.7 Auto-detect Overdue

**Job `MarkOverdueInvoices`:**
```php
Invoice::where('status', 'unpaid')
    ->where('due_date', '<', now()->startOfDay())
    ->update(['status' => 'overdue']);
```

**Schedule:** `Schedule::job(new MarkOverdueInvoices)->dailyAt('01:00')`

### 1.8 Invoice Number Race Condition Fix

Tambah DB lock pada `generateInvoiceNumber()`:
```php
DB::transaction(function () {
    // SELECT ... FOR UPDATE to lock the latest number
});
```

### 1.9 Pagination

Refactor `InvoiceController::index()` dari `->get()` ke `->paginate()` atau implementasi server-side DataTables (konsisten dengan ClientController yang sudah pakai DataTables).

---

## Phase 2: Notifikasi & Reminder

### 2.1 Email Queue

**Perubahan:**
- Refactor `Mail::to()->send()` ke `Mail::to()->queue()` di `InvoiceController::dispatchInvoiceDelivery()`
- Semua email dikirim async via queue

### 2.2 WhatsApp (Manual wa.me)

**Tidak berubah dari kondisi saat ini.** WhatsApp tetap manual via link `wa.me`. Perbaikan minor:
- Pastikan template pesan WhatsApp sudah include info tagihan yang lengkap (nomor invoice, total, jatuh tempo)
- Pastikan nomor phone dinormalisasi dengan benar

### 2.3 Sistem Reminder Otomatis

**Migration `create_invoice_reminders_table`:**
```
id, invoice_id (FK), reminder_type (before_due/after_due),
days_offset (int), channel (email/whatsapp),
sent_at (datetime), status (sent/failed/skipped),
error_message (nullable), created_at, updated_at
```

**Job `SendInvoiceReminders`:**
- Schedule: `dailyAt('08:00')`
- Logic:
  1. Ambil `reminder_days_before` dan `reminder_days_after` dari `setting()`
  2. Untuk setiap invoice `unpaid` / `overdue`:
     - Hitung selisih hari dari `due_date`
     - Cek apakah masuk di salah satu reminder stage
     - Cek apakah reminder untuk stage ini sudah pernah terkirim (tabel `invoice_reminders`)
     - Jika belum: kirim email (queue) dan/atau generate link WhatsApp
     - Catat ke `invoice_reminders`

**Channel handling:**
- `email`: kirim via Laravel Mail queue
- `whatsapp`: generate `wa.me` link, simpan di reminder record untuk staff buka manual. Atau tampilkan di dashboard "Reminder WhatsApp yang perlu dikirim hari ini"
- `both`: kedua channel

**Notification classes:**
- `InvoiceReminderNotification` — email reminder sebelum jatuh tempo
- `InvoiceOverdueNotification` — email setelah overdue

---

## Phase 3: Portal & Cetak

### 3.1 PDF Generation

- Install `barryvdh/laravel-dompdf`
- Buat `InvoicePdfService` 
- Route: `GET /invoices/{invoice}/download`
- Aktifkan `ClientPortalInvoiceController@download`

### 3.2 Client Portal - Payment Confirmation

- `POST /api/client-portal/invoices/{id}/payment-confirmation`
- Client upload bukti bayar → Payment record status `pending`
- Finance verifikasi dari admin panel

### 3.3 Pagination & Performance

- Server-side DataTables pada invoice list
- Database index pada `invoices(status, due_date, client_id)`

---

## Phase 4: Reporting & Audit

### 4.1 Laporan Keuangan

- `FinancialReportController` (permission sudah ada)
- Revenue summary, aging report (0-30, 31-60, 61-90, 90+)
- Export Excel/CSV

### 4.2 Enhanced Audit Trail

- `LogsModelActivity` pada `InvoiceItem` dan `Payment`
- Log semua event: generate, kirim, reminder, payment verify

---

## Pricing Flow (Diperjelas)

```
Package.price (harga paket per bulan)
  │
  ▼
Subscription
  ├── custom_price (harga khusus nego, nullable)
  ├── billing_period_months (1 = bulanan, 12 = tahunan, dll)
  ├── base_price = custom_price ?? (package.price × billing_period_months)
  ├── uses_ppn → ppn_amount = base_price × setting('billing.ppn_rate')/100
  ├── uses_pph23 → pph23_amount = base_price × setting('billing.pph23_rate')/100
  └── effective_price = base_price + ppn_amount - pph23_amount
        │
        ▼
      Invoice (full month)
        ├── subtotal_amount = base_price
        ├── tax_rate = setting('billing.ppn_rate') jika uses_ppn
        ├── tax_amount = ppn_amount
        ├── discount_amount = 0 (atau sesuai input manual)
        └── total_amount = effective_price
              │
              ▼
            InvoiceItem
              ├── description = "Langganan {paket} (Periode {bulan tahun})"
              ├── amount = base_price
              ├── qty = 1
              ├── total = base_price
              └── is_prorated = false

      Invoice (prorata)
        ├── subtotal_amount = prorated_base_price
        ├── tax_rate, tax_amount = dihitung dari prorated_base_price
        └── total_amount = prorated_effective_price
              │
              ▼
            InvoiceItem
              ├── description = "Prorata Langganan {paket} (15 Jul - 31 Jul, 17 hari)"
              ├── amount = (base_price / 31) × 17
              ├── qty = 1
              ├── total = prorated_amount
              ├── is_prorated = true
              ├── proration_start_date = 2026-07-15
              ├── proration_end_date = 2026-07-31
              └── proration_days = 17
```

---

## Prorata: Edge Cases & Rules

| Skenario | Behavior |
|----------|----------|
| Register tanggal 1 (= tanggal generate) | Full month, bukan prorata |
| Register di hari terakhir bulan | Prorata 1 hari |
| Upgrade di hari yang sama dengan generate | Full month harga baru, bukan prorata |
| Downgrade → selisih negatif | Kredit item (amount negatif) di invoice |
| Suspend → invoice belum bayar | Adjust invoice existing (kurangi total), bukan buat credit note baru |
| Suspend → invoice sudah bayar | Buat credit memo terpisah |
| Terminate → belum pernah ada invoice | Tidak ada aksi prorata |
| Prorata disabled di setting | Semua skenario di-skip, full month saja |
| Subscription tahunan (billing_period_months=12) | Prorata dari daily rate tahunan: base_price / 365 × hari aktif |

---

## File Baru

| File | Phase |
|------|-------|
| `database/migrations/xxxx_create_system_settings_table.php` | 1 |
| `app/Models/SystemSetting.php` | 1 |
| `database/seeders/SystemSettingSeeder.php` | 1 |
| `resources/views/settings/index.blade.php` (rewrite) | 1 |
| `app/Services/ProrataCalculationService.php` | 1 |
| `database/migrations/xxxx_add_proration_fields_to_invoice_items.php` | 1 |
| `database/migrations/xxxx_create_payments_table.php` | 1 |
| `database/migrations/xxxx_add_partially_paid_to_invoices.php` | 1 |
| `app/Models/Payment.php` | 1 |
| `app/Http/Controllers/PaymentController.php` | 1 |
| `resources/views/payments/index.blade.php` | 1 |
| `resources/views/payments/create.blade.php` | 1 |
| `app/Jobs/GenerateMonthlyInvoices.php` | 1 |
| `app/Jobs/MarkOverdueInvoices.php` | 1 |
| `database/migrations/xxxx_create_invoice_reminders_table.php` | 2 |
| `app/Jobs/SendInvoiceReminders.php` | 2 |
| `app/Notifications/InvoiceReminderNotification.php` | 2 |
| `app/Notifications/InvoiceOverdueNotification.php` | 2 |
| `app/Services/InvoicePdfService.php` | 3 |
| `app/Http/Controllers/FinancialReportController.php` | 4 |

## File yang Dimodifikasi

| File | Phase |
|------|-------|
| `app/Http/Controllers/SystemSettingController.php` | 1 |
| `app/Models/Subscription.php` | 1 |
| `app/Models/InvoiceItem.php` | 1 |
| `app/Http/Controllers/InvoiceController.php` | 1 |
| `app/Http/Controllers/SubscriptionController.php` | 1 |
| `app/Models/Invoice.php` | 1 |
| `resources/views/invoices/create.blade.php` | 1 |
| `resources/views/invoices/show.blade.php` | 1 |
| `resources/views/invoices/index.blade.php` | 1 |
| `resources/views/subscriptions/index.blade.php` | 1 |
| `resources/views/subscriptions/show.blade.php` | 1 |
| `database/seeders/PermissionSeeder.php` | 1 |
| `database/seeders/DatabaseSeeder.php` | 1 |
| `routes/web.php` | 1, 4 |
| `routes/console.php` | 1, 2 |
| `resources/views/layouts/sidebar.blade.php` | 1, 4 |
| `.env.example` | 1 |
| `app/Http/Controllers/Api/ClientPortal/ClientPortalInvoiceController.php` | 3 |
| `routes/client_portal_api.php` | 3 |

---

## Estimasi Timeline

| Phase | Scope | Durasi |
|-------|-------|--------|
| Phase 1 | SystemSetting + refactor tax + prorata + Payment + auto-generate + overdue + bug fixes | 3-4 minggu |
| Phase 2 | Email queue + reminder system | 1 minggu |
| Phase 3 | PDF + client portal payment + pagination | 1 minggu |
| Phase 4 | Laporan keuangan + audit trail | 1 minggu |
| **Total** | | **6-7 minggu** |
