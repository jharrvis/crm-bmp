<?php

namespace Database\Seeders;

use App\Models\InternetBackup;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class InternetBackupSeeder extends Seeder
{
    public function run(): void
    {
        $indibiz = Vendor::firstOrCreate(
            ['name' => 'PT Telkom Indonesia (Indibiz)'],
            ['notes' => 'Link Backup']
        );

        $indihome = Vendor::firstOrCreate(
            ['name' => 'PT. Telkomsel (Indihome)'],
            ['notes' => 'Link Backup']
        );

        $vendorMap = [
            'Indibiz' => $indibiz->id,
            'Indihome' => $indihome->id,
        ];

        $data = [
            ['vendor' => 'Indibiz', 'name' => 'PT. Bumi Merbabu Permai ( TMNK )', 'bandwidth_mbps' => 150, 'monthly_cost' => 782550, 'address' => 'Dusun Ngadirno, RT. 17 / RW. 04, Ngentak Kidul, Klero, Kec. Tengaran, Kabupaten Semarang', 'notes' => 'Perubhn harga 23 April 2026'],
            ['vendor' => 'Indihome', 'name' => 'Mess mutiara sewakul sk 1', 'bandwidth_mbps' => 150, 'monthly_cost' => 369300, 'address' => 'ki sarino ungaran barat', 'notes' => ''],
            ['vendor' => 'Indihome', 'name' => 'PT Semarang Garment', 'bandwidth_mbps' => 100, 'monthly_cost' => 330450, 'address' => 'Jl. Soekarno Hatta No.KM. 25, Krajan Kidul, Wujil, Kec. Bergas', 'notes' => ''],
            ['vendor' => 'Indihome', 'name' => 'PT. Sam Kyung Jaya Garments (sk 1)', 'bandwidth_mbps' => 150, 'monthly_cost' => 369300, 'address' => 'Jl. PTP Ngobo XVIII, Krajan Wringin Putih, Wringin Putih, Kec. Bergas', 'notes' => 'Proses pergantian indihome ke indibiz'],
            ['vendor' => 'Indibiz', 'name' => 'PT Nesia Pan Pacific Clothing ( TMNC )', 'bandwidth_mbps' => 150, 'monthly_cost' => 964423, 'address' => 'Jl. Nesia ketonggo, RT.01/RW.02, Dusun Ketonggo, Kerjo Lor, Kec. Ngadirojo, Kabupaten Wonogiri', 'notes' => ''],
            ['vendor' => 'Indibiz', 'name' => 'Café monte Gelato & Coffee', 'bandwidth_mbps' => 50, 'monthly_cost' => 434790, 'address' => 'Jl Diponegoro 35 A Salatiga', 'notes' => ''],
            ['vendor' => 'Indibiz', 'name' => 'PT Sam Kyung Jaya Busana ( Sk2 )', 'bandwidth_mbps' => 75, 'monthly_cost' => 534790, 'address' => 'Dukuh Tiris, RT.1/RW.12, Area Sawah/Kebun, Candi, Kec. Ampel, Kabupaten Boyolali', 'notes' => ''],
            ['vendor' => 'Indibiz', 'name' => 'PT. Bumi Merbabu Permai ( Kantor semarang )', 'bandwidth_mbps' => 50, 'monthly_cost' => 473970, 'address' => 'Jl. Timoho Raya Bulusan Tembalang Semarang', 'notes' => ''],
            ['vendor' => 'Indibiz', 'name' => 'PT. Starcam Apparel Indonesia', 'bandwidth_mbps' => 75, 'monthly_cost' => 523920, 'address' => 'Mindahan - Batealet - Jepara', 'notes' => 'Perubhn harga 23 April 2026'],
            ['vendor' => 'Indibiz', 'name' => 'Hotel Grand Mega Resort & Spa Cepu', 'bandwidth_mbps' => 100, 'monthly_cost' => 671550, 'address' => 'Jl. Raya Tambakromo No 27 Cepu', 'notes' => 'Perubhn harga 15 April 2026'],
            ['vendor' => 'Indibiz', 'name' => 'PT. Bumi Merbabu Permai ( Kantor Kudus )', 'bandwidth_mbps' => 50, 'monthly_cost' => 449550, 'address' => 'Ruko A.Yani No. 21, Panjunan Kudus', 'notes' => 'Perubhn harga 15 April 2026'],
            ['vendor' => 'Indibiz', 'name' => 'PT. Sadua Indo Bumi Merbabu Permai ( Hansoll Sadua )', 'bandwidth_mbps' => 100, 'monthly_cost' => 593850, 'address' => 'Dusun Gintungan RT.19 RW 11 Tluko Butuh Tengaran', 'notes' => 'Aktif 7 Februari 2026'],
            ['vendor' => 'Indibiz', 'name' => 'PT, Hansoll Indo Bumi Merbabu Permai ( Hansoll Klaten )', 'bandwidth_mbps' => 100, 'monthly_cost' => 593850, 'address' => 'Jl. Bugisan Raya RT 001 RW 006 Desa Bugisan kec, Prambanan Klaten', 'notes' => 'Aktif 3 Maret 2026'],
            ['vendor' => 'Indibiz', 'name' => 'PT. Hansoll Indo Java BMP ( Hansoll Boyolali)', 'bandwidth_mbps' => 300, 'monthly_cost' => 1254300, 'address' => 'Dukuh Ngemplak RT 006 RW 002 Randusari Teras Boyolali', 'notes' => 'Aktif 5 Mei 2026'],
            ['vendor' => 'Indibiz', 'name' => 'PK Mulyo', 'bandwidth_mbps' => 50, 'monthly_cost' => 394050, 'address' => 'Margorejo 588A Salatiga', 'notes' => 'Aktif 7 April 2026'],
            ['vendor' => 'Indibiz', 'name' => 'Gor Waikiki Bumi Merbabu Permai ( Back up metro FS )', 'bandwidth_mbps' => 75, 'monthly_cost' => 460650, 'address' => 'Jalan Ngesrep Barat Tinjomoyo Kec Banyumanik Semarang', 'notes' => 'Aktif 18 April 2026'],
        ];

        foreach ($data as $row) {
            InternetBackup::updateOrCreate(
                ['name' => $row['name'], 'vendor_id' => $vendorMap[$row['vendor']]],
                [
                    'bandwidth_mbps' => $row['bandwidth_mbps'],
                    'monthly_cost' => $row['monthly_cost'],
                    'address' => $row['address'],
                    'status' => 'active',
                    'notes' => $row['notes'] ?: null,
                ]
            );
        }

        $this->command->info('Internet Backup data imported: ' . count($data) . ' records.');
    }
}
