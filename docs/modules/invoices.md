# Modul Invoices

## Ringkasan

Modul invoice menangani:

- generate invoice bulanan dari langganan aktif
- pembuatan invoice manual satu kali
- status draft, unpaid, paid, overdue, cancelled
- penanda invoice sudah dikirim atau belum
- pengiriman manual via email dan/atau WhatsApp

## Invoice Manual

Halaman: `/invoices/create`

Fitur utama:

- pencarian pelanggan dengan dropdown search
- tanggal invoice manual
- pilihan jatuh tempo preset `7`, `14`, `30` hari atau custom tanggal
- item ringkas per baris:
  - sumber item
  - pilihan layanan/langganan
  - deskripsi
  - qty
  - harga
  - jumlah baris
- ringkasan subtotal, PPN 11%, discount, total
- catatan invoice
- tanda tangan:
  - tanpa tanda tangan
  - pilih signature yang sudah ada
  - upload signature baru

## Aksi Simpan

Form invoice manual mendukung:

- `draft`
- `confirm`
- `send`

Perilaku:

- `draft` menyimpan invoice dengan status `draft`
- `confirm` menyimpan invoice final dengan status `unpaid`
- `send` menyimpan invoice final lalu memproses kanal kirim yang dipilih

## Pengiriman

### Email

- memakai email kontak utama pelanggan
- subject dan body bisa diedit sebelum submit
- body dikirim menggunakan template email invoice internal

### WhatsApp

- hanya aktif jika pelanggan punya nomor `whatsapp` atau `phone`
- sistem membuat link `wa.me/{nomor}?text=...`
- setelah invoice berhasil dibuat, halaman detail invoice akan membuka link WhatsApp di tab baru

## Status Kirim

Daftar invoice menampilkan:

- `Terkirim` / `Belum Dikirim`
- badge kanal:
  - `Email`
  - `WhatsApp`

Field database terkait:

- `sent_at`
- `sent_via_email`
- `sent_via_whatsapp`

## Tanda Tangan

File signature disimpan di disk `public` pada folder:

```text
storage/app/public/invoice-signatures
```

Invoice akan menampilkan signature jika `signature_path` terisi.

## Catatan Desain

Invoice recurring sebaiknya dipisah dari form manual. Recurring membutuhkan:

- template item tetap
- aturan jadwal generate
- tanggal mulai/berhenti
- status pause/aktif
- audit generate otomatis

Untuk itu, recurring lebih cocok sebagai modul/template terpisah daripada toggle tambahan di form invoice manual.
