<?php

namespace App\Jobs\Concerns;

use App\DomainRegistrars\RegistrarStaleRecovery;
use App\Models\RegistrarOperation;

/**
 * Pemulihan operasi yang menggantung berstatus `processing`.
 *
 * P1 audit: bila worker mati/timeout di tengah API call, operasi tetap berstatus
 * `processing` dan job menolak menjalankan ulang operasi yang sudah processing (idempotency),
 * sehingga operasi tidak bisa dipulihkan lewat retry normal. Dengan lease/stale window
 * (config `domain-registrars.operation_stale_minutes`), operasi processing
 * yang melewati batas waktu ditandai `failed` sehingga aman untuk di-retry.
 *
 * P1 audit (lengkap): Scheduler `registrar:recover-stale-operations` juga menandai stale
 * sebagai `failed` + kirim notifikasi. Job tidak boleh mengubah `failed` secara otomatis — hanya
 * controller `retryOperation` yang mengubah `failed` → `queued` lewat petunjuk eksplisit.
 *
 * Digunakan di: UpdateDomainNameservers, EditDomainDnsRecord, SetDomainEpp.
 * Untuk pemulihan otomatis scheduled, lihat RegistrarStaleRecovery::markFailed() atau
 * command `registrar:recover-stale-operations`.
 */
trait RecoverableRegistrarOperation
{
    protected function staleOperationMinutes(): int
    {
        return max(1, (int) config('domain-registrars.operation_stale_minutes', 30));
    }

    protected function isStaleProcessing($op): bool
    {
        return RegistrarStaleRecovery::isStale($op);
    }

    protected function markStaleAsFailed($op): void
    {
        RegistrarStaleRecovery::markFailed($op);
    }

    /**
     * Claim operasi yang secara eksplisit diantrekan oleh request baru atau retry manual.
     * Update bersyarat mencegah dua worker memproses operasi yang sama.
     */
    protected function claimQueuedOperation(RegistrarOperation $op): bool
    {
        return RegistrarOperation::query()
            ->whereKey($op->id)
            ->where('status', 'queued')
            ->update([
                'status' => 'processing',
                'started_at' => now(),
                'completed_at' => null,
                'error_summary' => null,
                'response_payload_redacted' => null,
            ]) === 1;
    }
}
