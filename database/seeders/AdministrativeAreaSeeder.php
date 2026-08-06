<?php

namespace Database\Seeders;

use App\Models\AdministrativeArea;
use Illuminate\Database\Seeder;

class AdministrativeAreaSeeder extends Seeder
{
    private const DATA_PATH = 'database/data/administrative-areas';

    public function run(): void
    {
        $this->import('provinces.csv', AdministrativeArea::LEVEL_PROVINCE);
        $this->import('regencies.csv', AdministrativeArea::LEVEL_REGENCY, 'province_code');
        $this->import('districts.csv', AdministrativeArea::LEVEL_DISTRICT, 'regency_code');
        $this->import('villages.csv', AdministrativeArea::LEVEL_VILLAGE, 'district_code');
    }

    private function import(string $filename, string $level, ?string $parentColumn = null): void
    {
        $path = base_path(self::DATA_PATH.'/'.$filename);
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new \RuntimeException("Data wilayah tidak ditemukan: {$filename}");
        }

        $headers = fgetcsv($handle);
        $batch = [];
        $now = now();

        while (($row = fgetcsv($handle)) !== false) {
            $record = array_combine($headers, $row);
            $batch[] = [
                'code' => $record['code'],
                'parent_code' => $parentColumn ? $record[$parentColumn] : null,
                'level' => $level,
                'name' => $record['name'],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($batch) === 1000) {
                AdministrativeArea::upsert($batch, ['code'], ['parent_code', 'level', 'name', 'updated_at']);
                $batch = [];
            }
        }

        if ($batch !== []) {
            AdministrativeArea::upsert($batch, ['code'], ['parent_code', 'level', 'name', 'updated_at']);
        }

        fclose($handle);
    }
}
