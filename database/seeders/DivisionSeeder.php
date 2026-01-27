<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisions = [
            [
                'name' => 'Billing & Finance',
                'description' => 'Mengelola penagihan, pembayaran, dan keuangan perusahaan.',
            ],
            [
                'name' => 'NOC (Network Operation Center)',
                'description' => 'Memantau dan mengelola jaringan serta infrastruktur IT.',
            ],
            [
                'name' => 'Teknis Lapangan',
                'description' => 'Instalasi, perbaikan, dan pemeliharaan di lokasi pelanggan.',
            ],
            [
                'name' => 'Research & Development',
                'description' => 'Riset teknologi baru dan pengembangan produk layanan.',
            ],
            [
                'name' => 'Customer Service',
                'description' => 'Melayani keluhan dan pertanyaan pelanggan.',
            ],
        ];

        foreach ($divisions as $division) {
            Division::create($division);
        }
    }
}
