<?php

namespace App\Jobs;

use App\DomainRegistrars\DomainRegistrarManager;
use App\Jobs\Concerns\RecoverableRegistrarOperation;
use App\Models\RegistrarAccount;
use App\Models\RegistrarOperation;
use App\Models\SubscriptionDomain;
use App\Services\Admin\AdminNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UpdateDomainNameservers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, RecoverableRegistrarOperation;

    public int $tries = 2;
    public array $backoff = [60, 300];
    public int $timeout = 60;

    public function __construct(public int $subscriptionDomainId, public array $nameservers, public ?int $requestedBy = null)
    {
    }

    public function handle(DomainRegistrarManager $manager, AdminNotificationService $notifications): void
    {
        $domain = SubscriptionDomain::with('registrarAccount')->find($this->subscriptionDomainId);
        if (! $domain || ! $domain->registrar_account_id) {
            return;
        }

        $account = $domain->registrarAccount;
        if (! $account || ! $account->is_active) {
            $domain->update(['sync_status' => 'failed', 'sync_error_summary' => 'Akun registrar tidak aktif']);
            return;
        }

        if (! $manager->isEnabled() || ! $manager->canPerform('update_nameservers')) {
            Log::info('UpdateDomainNameservers skipped — mode not allowed', ['domain_id' => $domain->id, 'mode' => $manager->effectiveMode()]);
            return;
        }

        $lock = Cache::lock('registrar:op:domain:'.$domain->id, 60);
        if (! $lock->get()) {
            return;
        }

        $key = 'update_ns_'.$domain->id.'_'.md5(implode(',', array_values(array_filter($this->nameservers))));
        $op = RegistrarOperation::firstOrCreate(
            ['idempotency_key' => $key],
            [
                'registrar_account_id' => $account->id,
                'subscription_domain_id' => $domain->id,
                'operation_type' => 'update_nameservers',
                'status' => 'queued',
                'requested_by' => $this->requestedBy,
                'request_payload_redacted' => ['nameservers' => $this->nameservers],
            ]
        );

        // Job lama tidak boleh mengulangi mutasi. Operasi stale hanya ditandai failed
        // dan harus melalui retry manual sebelum kembali ke status queued.
        if ($this->isStaleProcessing($op)) {
            $this->markStaleAsFailed($op);
            $lock->release();

            return;
        }
        if (! $this->claimQueuedOperation($op)) {
            $lock->release();

            return;
        }

        try {
            $provider = $manager->providerFor($account);
            $result = $provider->updateNameservers($account, $domain->domain_name, $this->nameservers);

            if (! ($result['success'] ?? false)) {
                $op->update(['status' => 'failed', 'completed_at' => now(), 'error_summary' => $result['message'] ?? 'Gagal', 'response_payload_redacted' => ['message' => $result['message'] ?? '']]);
                $domain->update(['sync_status' => 'failed', 'sync_error_summary' => mb_substr($result['message'] ?? 'Update nameserver gagal', 0, 500)]);
                $notifications->notifyAdmins(
                    'domain_operation_failed',
                    "Update nameserver {$domain->domain_name} gagal",
                    "Update nameserver {$domain->domain_name} via {$account->name} gagal: ".($result['message'] ?? '-'),
                    ['subscription_domain_id' => $domain->id, 'domain_name' => $domain->domain_name, 'registrar_account_id' => $account->id]
                );
                return;
            }

            $op->update(['status' => 'completed', 'completed_at' => now(), 'response_payload_redacted' => ['nameservers' => $this->nameservers]]);
            $domain->update(['sync_status' => 'synced', 'sync_error_summary' => null, 'last_synced_at' => now()]);
            $account->update(['last_synced_at' => now(), 'last_error_at' => null, 'last_error_summary' => null]);
        } catch (\Throwable $e) {
            Log::error('UpdateDomainNameservers failed', ['domain_id' => $domain->id, 'error' => $e->getMessage()]);
            $op->update(['status' => 'failed', 'completed_at' => now(), 'error_summary' => mb_substr($e->getMessage(), 0, 500)]);
            throw $e;
        } finally {
            $lock->release();
        }
    }
}
