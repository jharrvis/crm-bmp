<?php

namespace App\Console\Commands;

use App\DomainRegistrars\RegistrarStaleRecovery;
use App\Models\RegistrarOperation;
use Illuminate\Console\Command;

class RecoverStaleRegistrarOperations extends Command
{
    protected $signature = 'registrar:recover-stale-operations';
    protected $description = 'Tandai operasi registrar yang menggantung (processing melewati stale window) menjadi failed agar bisa di-retry manual';

    public function handle(): int
    {
        $staleOps = RegistrarOperation::with('subscriptionDomain')
            ->where('status', 'processing')
            ->where('started_at', '<', RegistrarStaleRecovery::staleCutoff())
            ->get();

        foreach ($staleOps as $op) {
            RegistrarStaleRecovery::markFailed($op);
            $this->info("Operasi #{$op->id} ({$op->operation_type}) ditandai failed.");
        }

        if ($staleOps->isEmpty()) {
            $this->info('Tidak ada operasi registrar stale.');
        } else {
            $this->info($staleOps->count().' operasi stale ditandai failed. Notifikasi admin telah dikirim.');
        }

        return self::SUCCESS;
    }
}