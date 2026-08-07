<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Branch;
use App\Services\ClientCodeReconciliationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ReconcileClientCodes extends Command
{
    protected $signature = 'clients:reconcile-codes
                            {--branch= : ID cabang yang akan diperiksa}
                            {--all : Periksa seluruh cabang}
                            {--year=26 : Dua digit tahun untuk kode pelanggan lama}
                            {--dry-run : Simulasikan perubahan tanpa mengubah data (mode default)}
                            {--apply : Terapkan perubahan yang sudah direncanakan}
                            {--confirm : Konfirmasi eksplisit untuk mode apply}
                            {--allow-skipped : Izinkan apply jika ada kode dengan format yang tidak dikenali}';

    protected $description = 'Memeriksa dan menyelaraskan prefix client_code dengan branch_id secara aman.';

    public function handle(ClientCodeReconciliationService $reconciliation): int
    {
        $branchId = $this->option('branch');
        $apply = (bool) $this->option('apply');
        $year = (string) $this->option('year');

        if (filled($branchId) && ! ctype_digit((string) $branchId)) {
            $this->error('Opsi --branch harus berupa ID cabang numerik.');

            return self::INVALID;
        }

        if (blank($branchId) && ! $this->option('all')) {
            $this->error('Gunakan --branch={id} atau --all.');

            return self::INVALID;
        }

        if (filled($branchId) && ! Branch::query()->whereKey((int) $branchId)->exists()) {
            $this->error('Cabang yang dipilih tidak ditemukan.');

            return self::INVALID;
        }

        if (filled($branchId) && $this->option('all')) {
            $this->error('Gunakan salah satu: --branch={id} atau --all.');

            return self::INVALID;
        }

        if ($apply && $this->option('dry-run')) {
            $this->error('Opsi --apply tidak dapat digunakan bersama --dry-run.');

            return self::INVALID;
        }

        if ($apply && ! $this->option('confirm')) {
            $this->error('Mode apply memerlukan --confirm. Jalankan dry-run terlebih dahulu sebelum menerapkan perubahan.');

            return self::INVALID;
        }

        $clients = $this->clientQuery($branchId)->get(['id', 'branch_id', 'client_code']);
        $allClients = Client::query()->get(['id', 'branch_id', 'client_code']);
        $plan = $reconciliation->plan($clients, $year, $allClients);
        $reportPath = $this->writeReport($plan, $year, $branchId, $apply ? 'pending' : 'dry-run');

        $this->renderPlan($plan, $reportPath);

        if ($plan['conflicts'] !== []) {
            $this->error('Tidak ada data yang diubah karena ditemukan konflik kode target.');

            return self::FAILURE;
        }

        if (! $apply) {
            $this->comment('Dry-run selesai. Gunakan --apply --confirm setelah laporan diverifikasi.');

            return self::SUCCESS;
        }

        if ($plan['skipped'] !== [] && ! $this->option('allow-skipped')) {
            $this->error('Apply dibatalkan karena ada kode yang tidak dikenali. Periksa laporan atau gunakan --allow-skipped jika sudah diverifikasi.');

            return self::FAILURE;
        }

        try {
            $appliedPlan = DB::transaction(function () use ($branchId, $year, $reconciliation) {
                // Lock every client code because client_code is globally unique across branches.
                $allLockedClients = Client::query()->lockForUpdate()->get(['id', 'branch_id', 'client_code']);
                $lockedClients = $this->clientQuery($branchId)->lockForUpdate()->get(['id', 'branch_id', 'client_code']);
                $lockedPlan = $reconciliation->plan($lockedClients, $year, $allLockedClients);

                if ($lockedPlan['conflicts'] !== []) {
                    throw new RuntimeException('Data berubah saat proses berjalan dan menghasilkan konflik baru.');
                }

                if ($lockedPlan['skipped'] !== [] && ! $this->option('allow-skipped')) {
                    throw new RuntimeException('Ditemukan kode dengan format tidak dikenali saat data dikunci.');
                }

                $token = Str::lower(Str::random(12));
                foreach ($lockedPlan['changes'] as $change) {
                    DB::table('clients')
                        ->where('id', $change['id'])
                        ->where('client_code', $change['old_code'])
                        ->update(['client_code' => "TMP-CC-{$token}-{$change['id']}"]);
                }

                foreach ($lockedPlan['changes'] as $change) {
                    DB::table('clients')
                        ->where('id', $change['id'])
                        ->where('client_code', "TMP-CC-{$token}-{$change['id']}")
                        ->update(['client_code' => $change['new_code']]);
                }

                return $lockedPlan;
            });
        } catch (\Throwable $exception) {
            report($exception);
            $this->error('Perubahan dibatalkan. Tidak ada data pelanggan yang diperbarui: '.$exception->getMessage());

            return self::FAILURE;
        }

        $reportPath = $this->writeReport($appliedPlan, $year, $branchId, 'applied');
        $this->info("Berhasil memperbarui {$this->count($appliedPlan['changes'])} client_code.");
        $this->line("Laporan audit: storage/app/{$reportPath}");

        return self::SUCCESS;
    }

    private function clientQuery(?string $branchId)
    {
        return Client::query()
            ->when(filled($branchId), fn ($query) => $query->where('branch_id', (int) $branchId))
            ->orderBy('branch_id')
            ->orderBy('id');
    }

    private function renderPlan(array $plan, string $reportPath): void
    {
        if ($plan['changes'] !== []) {
            $this->table(['Client ID', 'Cabang', 'Kode Lama', 'Kode Baru'], $plan['changes']);
        } else {
            $this->info('Tidak ada client_code yang perlu diselaraskan.');
        }

        $this->line('Perubahan direncanakan: '.$this->count($plan['changes']));
        $this->line('Nomor baru dialokasikan: '.$this->count($plan['resolutions']));
        if ($plan['resolutions'] !== []) {
            $this->table(
                ['Client ID', 'Kode Target Awal', 'Kode Dialokasikan', 'Alasan'],
                collect($plan['resolutions'])->map(fn (array $resolution) => [
                    $resolution['id'],
                    $resolution['requested_code'],
                    $resolution['new_code'],
                    $resolution['reason'],
                ])->all()
            );
        }
        $this->line('Kode dilewati: '.$this->count($plan['skipped']));
        $this->line('Konflik: '.$this->count($plan['conflicts']));
        $this->line("Laporan audit: storage/app/{$reportPath}");
    }

    private function writeReport(array $plan, string $year, ?string $branchId, string $status): string
    {
        $path = 'client-code-reconciliation/'.now()->format('Ymd_His_u')."-{$status}.json";

        Storage::disk('local')->put($path, json_encode([
            'status' => $status,
            'generated_at' => now()->toIso8601String(),
            'branch_id' => filled($branchId) ? (int) $branchId : null,
            'year' => $year,
            ...$plan,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        return $path;
    }

    private function count(array $items): int
    {
        return count($items);
    }
}
