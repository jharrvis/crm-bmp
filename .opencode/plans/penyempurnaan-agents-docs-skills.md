# Plan: Penyempurnaan AGENTS.md, Skills, dan Dokumentasi Modul

Tanggal: 29 Juni 2026

---

## Ringkasan

Audit terhadap `AGENTS.md` dan dokumentasi repo menemukan beberapa gap:

1. AGENTS.md belum mencakup standar kode teknis, testing, branching, environment, dan queue
2. 5 CRM skills sudah ada di `.codex/skills/` tapi belum tersedia di `.agents/skills/` (opencode)
3. Hanya 3 dari 20+ modul yang terdokumentasi di `docs/modules/`
4. Section numbering inkonsisten dan daftar modul tidak sinkron dengan kode aktual

---

## Task 1: Port 5 CRM Skills ke opencode

### Sumber
Skills sudah ada di `C:\Users\ThinkPad\.codex\skills\`:
- `crm-doc-maintainer` (69 baris, kualitas baik)
- `crm-release-checker` (73 baris, kualitas baik)
- `crm-api-doc-writer` (74 baris, kualitas baik)
- `crm-permission-auditor` (107 baris, kualitas baik)
- `crm-activitylog-auditor` (109 baris, kualitas baik)

### Target
Copy ke `C:\Users\ThinkPad\.agents\skills\` dengan format SKILL.md yang kompatibel dengan opencode.

### Perubahan yang diperlukan
- Format frontmatter sudah kompatibel (name + description)
- Tidak perlu `openai.yaml` untuk opencode, cukup `SKILL.md`
- Review dan sesuaikan instruksi jika ada yang spesifik ke Codex CLI

### File yang dibuat
```
C:\Users\ThinkPad\.agents\skills\
├── crm-doc-maintainer\SKILL.md
├── crm-release-checker\SKILL.md
├── crm-api-doc-writer\SKILL.md
├── crm-permission-auditor\SKILL.md
└── crm-activitylog-auditor\SKILL.md
```

---

## Task 2: Update AGENTS.md

### 2.1 Section baru yang ditambahkan

#### Section baru: Standar Kode Teknis (setelah Section 10)

Isi:
- Naming convention: controller (singular PascalCase), model (singular PascalCase), migration (snake_case verb_table), view folder (plural kebab-case)
- Fat controller vs service class: logic bisnis kompleks atau reusable harus dipindah ke `app/Services/`
- Validation: gunakan FormRequest untuk validasi kompleks, inline `$request->validate()` untuk yang sederhana
- Error handling: gunakan try-catch pada DB transaction, return response yang konsisten
- Tidak boleh hardcode value yang bisa berubah (tax rate, due days, dll) — gunakan `config/`
- Gunakan Eloquent relationship, hindari raw query kecuali untuk performance kritis
- Setiap model bisnis utama harus pakai trait `LogsModelActivity`

#### Section baru: Standar Queue dan Job (setelah Standar Kode Teknis)

Isi:
- Kapan pakai queue: email, notifikasi, PDF generation, bulk operations, API call eksternal
- Kapan TIDAK pakai queue: operasi yang user butuh hasilnya langsung (CRUD response)
- Naming: `GenerateMonthlyInvoices`, `SendInvoiceReminder` (PascalCase, Verb + Noun)
- Lokasi: `app/Jobs/`
- Scheduled jobs didaftarkan di `routes/console.php`
- Monitor: `php artisan queue:work` harus running di production
- `composer dev` sudah menjalankan queue worker secara otomatis untuk development

#### Section baru: Standar Testing (setelah Queue)

Isi:
- Framework: PHPUnit (`composer test`)
- Kapan wajib test: logic kalkulasi (billing, tax, pricing), service class, API endpoint
- Kapan opsional: CRUD sederhana yang hanya proxy ke Eloquent, view rendering
- Naming: `test_can_create_invoice()`, `test_unauthorized_user_cannot_delete()` (snake_case, deskriptif)
- Lokasi: `tests/Feature/` untuk HTTP tests, `tests/Unit/` untuk logic murni
- Minimal: setiap service class baru harus punya unit test

#### Section baru: Standar Branching (sebelum Checklist Commit)

Isi:
- Default branch: `master`
- Feature branch: `feature/nama-fitur` (contoh: `feature/payment-recording`)
- Bugfix branch: `fix/deskripsi-bug` (contoh: `fix/invoice-number-race-condition`)
- Hotfix: langsung ke `master` jika urgent, wajib deploy note
- Merge strategy: squash merge untuk feature branch agar history bersih
- Jangan rebase/force-push branch yang sudah di-push kecuali ada alasan kuat

#### Section baru: Standar Environment (tambahan di Section 11)

Isi:
- Setiap env variable baru wajib ditambahkan ke `.env.example` dengan nilai default yang aman
- Kelompokkan env vars: App, Database, Mail, Queue, Zabbix, Client Portal, Billing, GitHub
- Jangan simpan default value yang production-specific di `.env.example`
- Jika env var bersifat secret, tambahkan placeholder seperti `your-key-here`

### 2.2 Section yang diperbaiki

#### Section 3.2 - Sinkronisasi daftar modul

Tambahkan modul yang belum terdaftar:
- Topology / Network Topology
- Ticket Canned Responses
- Client Portal Account Management
- Profile

Tambahkan sub-section "Known Permission Gaps":
```
Berikut permission yang sudah didefinisikan di PermissionSeeder tapi belum ada implementasinya:
- payments (view, create, update, delete, verify)
- financial_reports (view)
- work_orders (view, create, update, delete, assign, complete)
- towers (view, create, update, delete)
- odps (view, create, update, delete)
```

#### Section 12.0 - Skills

Update:
- Jelaskan bahwa skill tersedia di dua lokasi:
  - `C:\Users\ThinkPad\.codex\skills\` untuk Codex CLI
  - `C:\Users\ThinkPad\.agents\skills\` untuk opencode
- Tambahkan instruksi: "gunakan skill tool untuk memuat skill saat task cocok dengan deskripsinya"
- Hapus kata "Jika environment agent mendukung" — karena sudah didukung

#### Section numbering

Opsi perbaikan:
- Renumber: 12 (Commit), 13 (Production Push), 14 (Larangan), 15 (Prioritas Docs), 16 (Ringkasan)
- Sub-sections dari 12: 12.1-12.5 tetap, tapi 12.0 dijadikan bagian dari 12 atau dipindah
- Section baru masuk di antara 10-11 dengan nomor yang sesuai

---

## Task 3: Buat Dokumentasi Modul Prioritas Tinggi

### 3.1 docs/modules/clients.md

Konten berdasarkan audit kode:

- **Tujuan**: Manajemen data pelanggan ISP per cabang
- **Entitas**: Client, ClientContact, ClientPortalAccount
- **Route utama**: CRUD `/clients`, nested contacts, portal account management
- **Permission**: `clients.view`, `clients.create`, `clients.update`, `clients.delete`
- **Alur bisnis**: registrasi → assign cabang → generate client_code (`{branch_id}{yy}{sequence}`) → kelola kontak → buat subscription → buat portal account
- **Integrasi**: Subscription, Invoice, Ticket, Client Portal, Branch
- **Seeder**: SalatigaClientSeeder, SemarangClientSeeder, KudusInternetClientSeeder

### 3.2 docs/modules/subscriptions.md

- **Tujuan**: Manajemen langganan internet/hosting/domain per client
- **Entitas**: Subscription, SubscriptionConnectivity, SubscriptionHosting, SubscriptionDomain, SubscriptionTopology, SubscriptionTopologyHistory
- **Route utama**: CRUD `/subscriptions`
- **Permission**: `subscriptions.view`, `create`, `update`, `delete`, `suspend`, `activate`
- **Alur bisnis**: client aktif → pilih paket → buat subscription → set harga → billing cycle → generate invoice
- **Pricing model**: base_price, PPN 11%, PPh23 2%, effective_price, billing_period_months, discount
- **Integrasi**: Package, Invoice, Client, Topology, Router
- **Known issues**: `billing_cycle_day` dan `next_billing_date` belum digunakan di proses generate

### 3.3 docs/modules/tickets.md

- **Tujuan**: Sistem tiket support pelanggan
- **Entitas**: Ticket, TicketReply, TicketReplyAttachment, TicketActivity, TicketCannedResponse
- **Route utama**: CRUD `/tickets`, bulk-update, reply, canned responses
- **Permission**: `tickets.view`, `create`, `update`, `delete`, `assign`, `close`
- **Alur bisnis**: buat tiket → assign → reply (bubble chat UI) → resolve/close
- **Integrasi**: Client, ClientPortal API, Employee, TicketCannedResponse
- **Client Portal**: 5 API endpoint (list, create, show, reopen, reply)

### Proses pembuatan
1. Baca controller, model, route, view, seeder terkait secara menyeluruh
2. Tulis sesuai template Section 7 AGENTS.md
3. Pastikan informasi akurat berdasarkan kode (kode = sumber kebenaran)

### File yang dibuat
```
docs/modules/clients.md
docs/modules/subscriptions.md
docs/modules/tickets.md
```

---

## Task 4: Update File Pendukung

### docs/README.md
Tambahkan 3 modul baru ke index dokumentasi.

### CHANGELOG.md
Tambahkan entri untuk perubahan dokumentasi dan skills.

---

## Urutan Eksekusi

| Step | Task | Detail |
|------|------|--------|
| 1 | Port 5 skills | Copy SKILL.md ke `.agents/skills/` untuk setiap skill |
| 2 | Update AGENTS.md | Tambah 5 section baru, perbaiki 3 section, fix numbering |
| 3 | Buat docs/modules/clients.md | Berdasarkan audit ClientController, Client model, routes |
| 4 | Buat docs/modules/subscriptions.md | Berdasarkan audit SubscriptionController, Subscription model |
| 5 | Buat docs/modules/tickets.md | Berdasarkan audit TicketController, Ticket model |
| 6 | Update docs/README.md | Tambah link ke 3 modul baru |
| 7 | Update CHANGELOG.md | Catat perubahan dokumentasi |

---

## Catatan

- Tidak ada migration/seeder yang dibutuhkan
- Tidak ada perubahan kode aplikasi
- Semua perubahan adalah dokumentasi dan konfigurasi skill
- Commit message yang disarankan: `Improve AGENTS.md standards, port CRM skills to opencode, add module docs`
