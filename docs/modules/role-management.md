# Role Management

## Tujuan

Mengelola role, permission, dan visibilitas akses menu/modul di aplikasi CRM.

## Entitas Terkait

- `Role`
- `Permission`
- `User`

## Route Utama

- `GET /roles`
- `GET /roles/{role}`
- `GET /roles/{role}/edit`
- `POST /roles`
- `PUT /roles/{role}`
- `DELETE /roles/{role}`
- `POST /roles/{role}/permissions`

## Permission

- `roles.view`
- `roles.create`
- `roles.update`
- `roles.delete`

## Alur Bisnis

1. Admin atau Owner membuat role baru atau mengubah role yang ada.
2. Permission dipilih per modul dan aksi.
3. User diberi role melalui modul `Karyawan`.
4. Sidebar dan akses halaman mengikuti permission yang dimiliki user.

## Integrasi Modul Lain

- `PermissionSeeder`
- `EmployeeController`
- `sidebar.blade.php`
- halaman `Activity Log`

## Catatan

- Role sistem tidak boleh dihapus.
- Perubahan role dan sinkronisasi permission tercatat di `Activity Log`.
- Halaman daftar role menampilkan jumlah user per role; daftar user hanya tampil di halaman detail role.
