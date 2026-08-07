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
     * @return array{changes: array<int, array<string, mixed>>, skipped: array<int, array<string, mixed>>, conflicts: array<int, array<string, mixed>>}
     */
    public function plan(Collection $clients, string $year = '26'): array
    {
        if (! preg_match('/^\d{2}$/', $year)) {
            throw new InvalidArgumentException('Tahun kode harus terdiri dari dua digit.');
        }

        $changes = [];
        $skipped = [];
        $ownersByCode = $clients->keyBy(fn (object $client) => (string) $client->client_code);
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

            $targetCode = sprintf('%d%s%s', $client->branch_id, $year, $matches['sequence']);

            if ($currentCode === $targetCode) {
                continue;
            }

            $changes[] = [
                'id' => $client->id,
                'branch_id' => $client->branch_id,
                'old_code' => $currentCode,
                'new_code' => $targetCode,
            ];
            $changeIdsByTarget[$targetCode][] = $client->id;
        }

        $conflicts = [];
        foreach ($changeIdsByTarget as $targetCode => $clientIds) {
            if (count($clientIds) > 1) {
                $conflicts[] = [
                    'new_code' => $targetCode,
                    'client_ids' => $clientIds,
                    'reason' => 'Lebih dari satu pelanggan menghasilkan kode target yang sama.',
                ];
            }
        }

        $changingClientIds = collect($changes)->pluck('id')->flip();
        foreach ($changes as $change) {
            $owner = $ownersByCode->get($change['new_code']);

            if ($owner && ! $changingClientIds->has($owner->id)) {
                $conflicts[] = [
                    'new_code' => $change['new_code'],
                    'client_ids' => [$change['id'], $owner->id],
                    'reason' => 'Kode target sudah dipakai pelanggan yang tidak ikut diubah.',
                ];
            }
        }

        return compact('changes', 'skipped', 'conflicts');
    }
}
