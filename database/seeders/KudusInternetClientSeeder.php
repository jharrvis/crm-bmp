<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KudusInternetClientSeeder extends Seeder
{
    /**
     * Source: "Data user KUDUS 2026.xlsb"
     * Imported only from the internet section:
     * "DATA ALL USER INTERNET BMP.NET KUDUS 2026"
     */
    public function run(): void
    {
        $branchId = 3;
        $registeredAt = Carbon::create(2026, 1, 1)->toDateString();

        $branch = Branch::find($branchId);

        if (!$branch) {
            $this->command?->error("Branch dengan ID {$branchId} tidak ditemukan.");
            return;
        }

        $rows = [
            ['name' => 'ADA Swalayan Kudus', 'bts' => 'Kantor', 'bw' => '13', 'ip' => '114.30.82.94'],
            ['name' => 'ADA Swalayan Pati', 'bts' => 'Colo', 'bw' => '10', 'ip' => '10.30.82.162'],
            ['name' => 'Gudang ADA Swalayan', 'bts' => 'Kantor', 'bw' => null, 'ip' => '114.30.82.84'],
            ['name' => 'W. Elite Pasar Brayung', 'bts' => 'Kantor', 'bw' => 'Up to 10', 'ip' => '114.30.82.163'],
            ['name' => 'BPR Pati ( sewa BTS )', 'bts' => 'Colo', 'bw' => null, 'ip' => null],
            ['name' => 'PT.Merdeka Panji Mulia', 'bts' => 'Kantor', 'bw' => 'Up To 11', 'ip' => '114.30.82.252'],
            ['name' => 'PT. Starcam', 'bts' => 'FO Icon', 'bw' => '50', 'ip' => '114.30.86.194'],
            ['name' => 'TK. Listrik Kartika', 'bts' => 'Kantor', 'bw' => 'Up to 10', 'ip' => '114.30.82.35'],
            ['name' => 'TK. Listrik Kartika - Rumah', 'bts' => 'kantor', 'bw' => null, 'ip' => '114.30.82.37'],
            ['name' => 'Pak Petrus (Tk.Mas Jago) - Welahan', 'bts' => 'Kantor', 'bw' => '15', 'ip' => '114.30.82.210'],
            ['name' => 'Jago Karanganyar', 'bts' => 'Kantor', 'bw' => null, 'ip' => '114.30.82.212'],
            ['name' => 'Jago Dempet', 'bts' => 'Kantor', 'bw' => null, 'ip' => '114.30.82.213'],
            ['name' => 'Jago Panjunan', 'bts' => 'Kantor', 'bw' => null, 'ip' => '114.30.82.212'],
            ['name' => 'Jago Gajah', 'bts' => 'Kantor Jago welahan', 'bw' => null, 'ip' => '114.30.82.210:8287'],
            ['name' => 'Jago Mayong', 'bts' => 'Kantor Jago welahan', 'bw' => null, 'ip' => '114.30.82.210:8296'],
            ['name' => 'Jago Bintoro', 'bts' => 'Kantor Jago welahan', 'bw' => null, 'ip' => '114.30.82.210:8298'],
            ['name' => 'Jago Buyaran', 'bts' => 'Jago Bintoro', 'bw' => null, 'ip' => '114.30.82.210:8282'],
            ['name' => 'Jago Pasar Welahan', 'bts' => 'Kantor Jago welahan', 'bw' => null, 'ip' => '114.30.82.210:8293'],
            ['name' => 'Jago Pasar Jetak', 'bts' => 'Kantor Jago welahan', 'bw' => null, 'ip' => '114.30.82.210:8285'],
            ['name' => 'Hotel Griptha', 'bts' => 'Kantor', 'bw' => 'Up To 11', 'ip' => '114.30.82.243'],
            ['name' => 'Kantor Jago Lingkar', 'bts' => 'Kantor', 'bw' => 'Up To 10', 'ip' => '114.30.82.248'],
            ['name' => 'Siti Fathiyah - 1', 'bts' => 'Colo', 'bw' => 'Up To 11', 'ip' => '114.30.82.250'],
            ['name' => 'Siti Fathiyah - 2', 'bts' => null, 'bw' => null, 'ip' => '114.30.82.251'],
            ['name' => 'PT. DCP Travelling Product', 'bts' => 'FO - LA', 'bw' => '120', 'ip' => '114.30.84.162'],
            ['name' => 'PT. Jinlin Lagguge Indonesia', 'bts' => 'FO - LA', 'bw' => '200', 'ip' => '114.30.84.170'],
            ['name' => 'PT. Donglong Textile Indonesia', 'bts' => 'FO - LA', 'bw' => '50', 'ip' => '114.30.84.81'],
        ];

        $created = 0;
        $updated = 0;

        DB::transaction(function () use ($rows, $branchId, $registeredAt, &$created, &$updated) {
            foreach ($rows as $row) {
                $existing = Client::query()
                    ->where('branch_id', $branchId)
                    ->where('name', $row['name'])
                    ->first();

                $payload = [
                    'branch_id' => $branchId,
                    'name' => $row['name'],
                    'type' => $this->resolveClientType($row['name']),
                    'status' => 'active',
                    'city' => 'Kudus',
                    'address' => 'Kudus, Jawa Tengah',
                    'registered_at' => $existing?->registered_at?->toDateString() ?? $registeredAt,
                    'notes' => $this->buildNotes($row),
                ];

                if ($existing) {
                    $existing->update($payload);
                    $updated++;
                    continue;
                }

                Client::create([
                    ...$payload,
                    'client_code' => $this->generateClientCode($branchId, $registeredAt),
                ]);

                $created++;
            }
        });

        $this->command?->info("Seeder pelanggan internet Kudus selesai. Created: {$created}, Updated: {$updated}.");
    }

    private function buildNotes(array $row): string
    {
        $lines = [
            'Imported from DATA ALL USER INTERNET BMP.NET KUDUS 2026',
        ];

        if (!empty($row['bts'])) {
            $lines[] = 'BTS: ' . $row['bts'];
        }

        if (!empty($row['bw'])) {
            $lines[] = 'BW: ' . $row['bw'];
        }

        if (!empty($row['ip'])) {
            $lines[] = 'IP: ' . $row['ip'];
        }

        return implode("\n", $lines);
    }

    private function resolveClientType(string $name): string
    {
        $businessKeywords = [
            'PT',
            'HOTEL',
            'BPR',
            'SWALAYAN',
            'GUDANG',
            'KANTOR',
            'TK.',
            'W.',
        ];

        $normalized = strtoupper($name);

        foreach ($businessKeywords as $keyword) {
            if (str_contains($normalized, $keyword)) {
                return 'business';
            }
        }

        return 'personal';
    }

    private function generateClientCode(int $branchId, string $registeredAt): string
    {
        $year = Carbon::parse($registeredAt)->format('y');
        $prefix = sprintf('%d%s', $branchId, $year);

        $latestMatchingCode = Client::query()
            ->where('branch_id', $branchId)
            ->where('client_code', 'like', $prefix . '%')
            ->select('client_code')
            ->orderByDesc('client_code')
            ->value('client_code');

        $nextNumber = 1;

        if ($latestMatchingCode && preg_match('/^' . preg_quote($prefix, '/') . '(\d{3})$/', $latestMatchingCode, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        do {
            $clientCode = sprintf('%s%03d', $prefix, $nextNumber);
            $nextNumber++;
        } while (Client::query()->where('client_code', $clientCode)->exists());

        return $clientCode;
    }
}
