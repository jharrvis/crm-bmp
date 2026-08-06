# Cabang

## Tujuan

Mengelola cabang operasional BMPnet, termasuk data default wilayah layanan yang dipakai saat membuat pelanggan baru.

## Route Utama

| Method | URI | Controller | Permission |
|---|---|---|
| GET | `/branches` | `BranchController@index` | `branches.view` |
| POST | `/branches` | `BranchController@store` | `branches.create` |
| PUT/PATCH | `/branches/{branch}` | `BranchController@update` | `branches.update` |
| DELETE | `/branches/{branch}` | `BranchController@destroy` | `branches.delete` |

## Data Wilayah Layanan

Setiap cabang dapat memiliki `default_province_code`, `default_regency_code`, `default_latitude`, dan `default_longitude`.

| Kode Cabang | Provinsi | Kabupaten/Kota |
|---|---|---|
| `SLT` | Jawa Tengah (`33`) | Kota Salatiga (`33.73`) |
| `SMG` | Jawa Tengah (`33`) | Kota Semarang (`33.74`) |
| `KDS` | Jawa Tengah (`33`) | Kabupaten Kudus (`33.19`) |

Form pelanggan hanya menerapkan default ini bila kode provinsi dan kabupaten/kota pelanggan masih kosong. Pilihan yang sudah tersimpan tidak ditimpa.

## Integrasi

- Modul Clients memakai default wilayah cabang untuk nilai awal alamat administratif dan pusat modal peta.
