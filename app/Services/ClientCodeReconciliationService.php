<?php

namespace App\Services;

use Illuminate\Support\Collection;
use InvalidArgumentException;

class ClientCodeReconciliationService
{
    /**
     * Build a safe plan without changing any client records.
     *
     * @param Collection<int, object> $clients
     * @param Collection<int, object>|null $allClients
     * @return array{changes: array<int, array<string, mixed>>, skipped: array<int, array<string, mixed>>, conflicts: array<int, array<string, mixed>>, resolutions: array<int, array<string, mixed>>}
     */
    public function plan(Collection $clients, string $year = '26', ?Collection $allClients = null): array
    {
        if (! preg_match('/^\d{2}$/', $year)) {
            throw new InvalidArgumentException('Tahun kode harus terdiri dari dua digit.');
        }

        $allClients ??= $clients;
        $changes = [];
        $skipped = [];
        $conflicts = [];
        $resolutions = [];
        $ownersByCode = $allClients->keyBy(fn (object $client) => trim((string) $client->client_code));
        $usedCodes = $ownersByCode->keys()->flip()->all();
        $changeIdsByTarget = [];
        $pattern = '/^\d+'.preg_quote($year, '/').'(?<sequence>\d{3})$/';

        foreach ($clients as $client) {
            $currentCode = trim((string) $client->client_code);

            if (! preg_match($pattern, $currentCode, $matches)) {
                $skipped[] = [
                    'id' => $client->id,
                    'branch_id' => $client->branch_id,
                    'old_code' => $currentCode,
                    'reason' => "Kode tidak sesuai format numerik legacy *{$year}XXX.",
                ];

                continue;
            }

            $requestedCode = sprintf('%d%s%s', $client->branch_id, $year, $matches['sequence']);

            if ($currentCode === $requestedCode) {
                continue;
            }

            $targetCode = $requestedCode;
            $owner = $ownersByCode->get($targetCode);

            if (($owner && (int) $owner->id !== (int) $client->id) || isset($changeIdsByTarget[$targetCode])) {
                $targetCode = $this->nextAvailableCode((int) $client->branch_id, $year, $usedCodes);

                if ($targetCode === null) {
                    $conflicts[] = [
                        'new_code' => $requestedCode,
                        'client_ids' => [$client->id],
                        'reason' => 'Tidak ada nomor urut tiga digit yang tersisa untuk cabang dan tahun ini.',
                    ];

                    continue;
                }

                $resolutions[] = [
                    'id' => $client->id,
                    'branch_id' => $client->branch_id,
                    'old_code' => $currentCode,
                    'requested_code' => $requestedCode,
                    'new_code' => $targetCode,
                    'reason' => 'Kode target awal sudah dipakai; dialihkan ke nomor urut kosong berikutnya.',
                ];
            }

            $changes[] = [
                'id' => $client->id,
                'branch_id' => $client->branch_id,
                'old_code' => $currentCode,
                'new_code' => $targetCode,
            ];
            $changeIdsByTarget[$targetCode][] = $client->id;
            $usedCodes[$targetCode] = true;
        }

        foreach ($changeIdsByTarget as $targetCode => $clientIds) {
            if (count($clientIds) > 1) {
                $conflicts[] = [
                    'new_code' => $targetCode,
                    'client_ids' => $clientIds,
                    'reason' => 'Lebih dari satu pelanggan menghasilkan kode target yang sama.',
                ];
            }
        }

        return compact('changes', 'skipped', 'conflicts', 'resolutions');
    }

    /**
     * Use the next sequence after the latest allocated code to preserve numbering history.
     *
     * @param array<string, bool> $usedCodes
     */
    private function nextAvailableCode(int $branchId, string $year, array $usedCodes): ?string
    {
        $prefix = "{$branchId}{$year}";
        $maxSequence = 0;

        foreach (array_keys($usedCodes) as $code) {
            if (preg_match('/^'.preg_quote($prefix, '/').'(\d{3})$/', $code, $matches)) {
                $maxSequence = max($maxSequence, (int) $matches[1]);
            }
        }

        for ($sequence = $maxSequence + 1; $sequence <= 999; $sequence++) {
            $candidate = $prefix.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
            if (! isset($usedCodes[$candidate])) {
                return $candidate;
            }
        }

        // If the latest sequence is already 999, use a historical empty slot rather than failing.
        for ($sequence = 1; $sequence <= $maxSequence; $sequence++) {
            $candidate = $prefix.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
            if (! isset($usedCodes[$candidate])) {
                return $candidate;
            }
        }

        return null;
    }
}
