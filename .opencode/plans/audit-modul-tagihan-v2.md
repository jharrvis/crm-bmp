# Plan: Modul Tagihan (Invoice/Billing) v2

Tanggal: 30 Juni 2026
Update dari: audit-modul-tagihan.md

---

## Perubahan dari Plan v1

1. **Pricing**: Diperjelas bahwa harga layanan bisa pakai harga paket atau harga khusus (nego). `custom_price` sudah ada di Subscription, ini hanya perlu dipastikan konsisten di generate.
2. **WhatsApp**: Tetap manual via `wa.me/62xxx` link di browser, bukan API. Tidak ada integrasi WhatsApp API.
3. **Semua konfigurasi billing via UI**: PPN rate, PPh23 rate, due days, tanggal generate, reminder schedule — semua disimpan di tabel `system_settings` dan dikelola dari menu Pengaturan Sistem (bukan hardcoded).

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

## Phase 1: Fondasi Billing

### 1.1 SystemSetting (Model + Migration + Seeder + UI)

**Migration `create_system_settings_table`:**
```
id, group, key (unique), value (text nullable), type, description, timestamps
```

**Seeder `SystemSettingSeeder`:**
- Seed default billing settings (tabel di atas)
- Safe re-run: gunakan `updateOrCreate`

**Controller `SystemSettingController`:**
- Refactor dari stub menjadi full CRUD
- `index()`: tampilkan semua settings grouped
- `update()`: update value per key atau bulk per group
- Permission: `settings.view`, `settings.update`

**View `settings/index.blade.php`:**
- Tab/section per group (awalnya hanya `billing`)
- Form input sesuai type: number untuk float/integer, toggle untuk boolean, textarea untuk json
- Validasi: PPN/PPh23 range 0-100, due days min 1, generate day 1-28

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

### 1.4 Payment Model & Recording

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

### 1.5 Auto-generate Invoice Bulanan

**Job `GenerateMonthlyInvoices`:**
- Query active subscriptions
- Tanggal generate: `setting('billing.auto_generate_day')` — configurable dari UI
- Dedup: cek apakah invoice bulan ini sudah ada
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

Gunakan `dailyAt` + check day agar lebih fleksibel daripada `monthlyOn` yang tidak bisa baca dari database.

### 1.6 Auto-detect Overdue

**Job `MarkOverdueInvoices`:**
```php
Invoice::where('status', 'unpaid')
    ->where('due_date', '<', now()->startOfDay())
    ->update(['status' => 'overdue']);
```

**Schedule:** `Schedule::job(new MarkOverdueInvoices)->dailyAt('01:00')`

### 1.7 Invoice Number Race Condition Fix

Tambah DB lock pada `generateInvoiceNumber()`:
```php
DB::transaction(function () {
    // SELECT ... FOR UPDATE to lock the latest number
});
```

### 1.8 Pagination

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
      Invoice
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
              └── total = base_price
```

**Catatan**: Harga subscription sudah support:
- Harga paket default (`package.price`)
- Harga khusus/nego (`custom_price`)
- Multi-periode (`billing_period_months`: 1 bulan, 3 bulan, 6 bulan, 12 bulan)
- PPN on/off per subscription
- PPh23 on/off per subscription

Ini tidak perlu diubah. Yang perlu diperbaiki adalah cara `generate()` menyimpan data ke invoice agar tax breakdown konsisten.

---

## File Baru

| File | Phase |
|------|-------|
| `database/migrations/xxxx_create_system_settings_table.php` | 1 |
| `app/Models/SystemSetting.php` | 1 |
| `database/seeders/SystemSettingSeeder.php` | 1 |
| `resources/views/settings/index.blade.php` (rewrite) | 1 |
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
| `app/Http/Controllers/InvoiceController.php` | 1 |
| `app/Models/Invoice.php` | 1 |
| `resources/views/invoices/create.blade.php` | 1 |
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
| Phase 1 | SystemSetting + refactor tax + Payment + auto-generate + overdue + bug fixes | 2-3 minggu |
| Phase 2 | Email queue + reminder system | 1 minggu |
| Phase 3 | PDF + client portal payment + pagination | 1 minggu |
| Phase 4 | Laporan keuangan + audit trail | 1 minggu |
| **Total** | | **5-6 minggu** |
