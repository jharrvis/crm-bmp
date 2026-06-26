# Dokumentasi BMPnet CRM

Dokumentasi ini menjadi sumber referensi utama untuk:

- modul aplikasi
- endpoint API
- deployment note
- permission matrix
- standar pengembangan

## Struktur

- `api/`
  - dokumentasi endpoint, terutama untuk integrasi Client Portal
- `modules/`
  - dokumentasi modul internal CRM
  - termasuk modul `Activity Log` dan TODO operasionalnya
- `deployment.md`
  - catatan deployment, cache clear, migration, dan seeder
- `permission-matrix.md`
  - mapping menu, permission, dan role default

## Prinsip

1. Dokumentasi ini adalah sumber kebenaran yang dibaca dari repo.
2. Halaman `Dokumentasi` di aplikasi hanya menampilkan isi folder ini.
3. Setiap perubahan fitur yang penting harus mengupdate dokumen terkait.
4. Perubahan API wajib mengupdate file di `docs/api/`.
