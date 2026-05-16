<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [
            [
                'name' => 'Cabang Salatiga',
                'code' => 'SLT',
                'address' => 'Jl. Jenderal Sudirman No. 123, Salatiga',
                'phone' => '0298-123456',
            ],
            [
                'name' => 'Cabang Semarang',
                'code' => 'SMG',
                'address' => 'Jl. Pemuda No. 456, Semarang',
                'phone' => '024-987654',
            ],
            [
                'name' => 'Cabang Kudus',
                'code' => 'KDS',
                'address' => 'Jl. Sunan Kudus No. 789, Kudus',
                'phone' => '0291-555666',
            ],
        ];

        foreach ($branches as $branch) {
            Branch::updateOrCreate(['code' => $branch['code']], $branch);
        }
    }
}
