<?php

namespace App\Jobs;

use App\DomainRegistrars\DomainRegistrarManager;
use App\Jobs\Concerns\RecoverableRegistrarOperation;
use App\Models\RegistrarOperation;
use App\Services\Admin\AdminNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SetDomainEpp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, RecoverableRegistrarOperation;

    public int $tries = 2;
    public array $backoff = [60, 300];
    public int $timeout = 60;

    /**
     * Job hanya membawa operation ID — EPP code disimpan terenkripsi
     * di registrar_operations.request_secret_encrypted (dibaca saat eksekusi,
     * dihapus setelah sukses). Tidak pernah ada secret di payload queue.
     */
    public function __construct(public int $operationId)
    {
    }

    public function handle(DomainRegistrarManager $manager, AdminNotificationService $notifications): void
    {
        $op = RegistrarOperation::with(['subscriptionDomain.registrarAccount'])->find($this->operationId);
        if (! $op) {
            return;
        }

        $domain = $op->subscriptionDomain;
        $account = $op->subscriptionDomain?->registrarAccount;
        if (! $domain || ! $account || ! $domain->registrar_account_id) {
            return;
        }

        // Job lama tidak boleh mengulangi mutasi. Operasi stale hanya ditandai failed
        // dan harus melalui retry manual sebelum kembali ke status queued.
        if ($this->isStaleProcessing($op)) {
            $this->markStaleAsFailed($op);

            return;
        }

        if (! $account->is_active) {
            $domain->update(['sync_status' => 'failed', 'sync_error_summary' => 'Akun registrar tidak aktif']);
            return;
        }

        if (! $manager->isEnabled() || ! $manager->canPerform('set_epp')) {
            Log::info('SetDomainEpp skipped — mode not allowed', ['operation_id' => $op->id, 'mode' => $manager->effectiveMode()]);
            return;
        }

        $lock = Cache::lock('registrar:op:domain:'.$domain->id, 60);
        if (! $lock->get()) {
            return;
        }

        if (! $this->claimQueuedOperation($op)) {
            $lock->release();

            return;
        }

        $secret = $op->request_secret_encrypted;
        $eppCode = $secret ? decrypt($secret) : null;
        if (blank($eppCode)) {
            $op->update(['status' => 'failed', 'completed_at' => now(), 'error_summary' => 'Payload EPP tidak tersedia. Ulangi dari form Ganti EPP.']);
            $lock->release();

            return;
        }

        try {
            $provider = $manager->providerFor($account);
            $result = $provider->setEpp($account, $domain->domain_name, $eppCode);

            if (! ($result['success'] ?? false)) {
                $op->update(['status' => 'failed', 'completed_at' => now(), 'error_summary' => $result['message'] ?? 'Gagal', 'response_payload_redacted' => ['message' => $result['message'] ?? '']]);
                $domain->update(['sync_status' => 'failed', 'sync_error_summary' => mb_substr($result['message'] ?? 'Ganti EPP gagal', 0, 500)]);
                $notifications->notifyAdmins(
                    'domain_operation_failed',
                    "Ganti EPP {$domain->domain_name} gagal",
                    "Ganti EPP code {$domain->domain_name} via {$account->name} gagal: ".($result['message'] ?? '-'),
                    ['subscription_domain_id' => $domain->id, 'domain_name' => $domain->domain_name, 'registrar_account_id' => $account->id]
                );

                return;
            }

            // Simpan EPP baru terenkripsi (JANGAN log nilainya), lalu hapus secret dari operasi.
            $domain->update([
                'auth_code_encrypted' => encrypt($eppCode),
                'sync_status' => 'synced',
                'sync_error_summary' => null,
                'last_synced_at' => now(),
            ]);
            $op->update([
                'status' => 'completed',
                'completed_at' => now(),
                'request_secret_encrypted' => null,
                'response_payload_redacted' => ['epp_changed' => true],
            ]);
            $account->update(['last_synced_at' => now(), 'last_error_at' => null, 'last_error_summary' => null]);
        } catch (\Throwable $e) {
            Log::error('SetDomainEpp failed', ['operation_id' => $op->id, 'error' => $e->getMessage()]);
            $op->update(['status' => 'failed', 'completed_at' => now(), 'error_summary' => mb_substr($e->getMessage(), 0, 500)]);
            throw $e;
        } finally {
            $lock->release();
        }
    }
}
