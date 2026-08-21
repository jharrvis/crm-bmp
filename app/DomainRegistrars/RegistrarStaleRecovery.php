<?php

namespace App\DomainRegistrars;

use App\Models\RegistrarOperation;
use App\Services\Admin\AdminNotificationService;
use Illuminate\Support\Facades\Log;

class RegistrarStaleRecovery
{
    public static function staleCutoff(): \DateTimeInterface
    {
        return now()->subMinutes(max(1, (int) config('domain-registrars.operation_stale_minutes', 30)));
    }

    public static function isStale(RegistrarOperation $op): bool
    {
        return $op->status === 'processing'
            && $op->started_at instanceof \DateTimeInterface
            && $op->started_at->lt(static::staleCutoff());
    }

    public static function markFailed(RegistrarOperation $op): void
    {
        $op->update([
            'status' => 'failed',
            'completed_at' => now(),
            'error_summary' => 'Operasi menggantung berstatus processing (worker timeout) — ditandai failed otomatis. Tinjau lalu tekan retry.',
        ]);

        Log::warning('Registrar operation stale — marked failed', [
            'operation_id' => $op->id,
            'operation_type' => $op->operation_type,
        ]);

        $domain = $op->subscriptionDomain;
        if ($domain) {
            app(AdminNotificationService::class)->notifyAdmins(
                'domain_operation_failed',
                'Operasi domain menggantung (stale)',
                "Operasi {$op->operation_type} untuk {$domain->domain_name} berstatus processing terlalu lama dan ditandai failed otomatis. Tinjau lalu tekan retry manual.",
                ['subscription_domain_id' => $op->subscription_domain_id, 'domain_name' => $domain->domain_name, 'operation_id' => $op->id]
            );
        }
    }
}