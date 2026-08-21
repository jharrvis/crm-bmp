<?php

namespace App\Jobs;

use App\DomainRegistrars\DomainRegistrarManager;
use App\Models\RegistrarAccount;
use App\Models\SubscriptionDomain;
use App\Services\Admin\AdminNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncRegistrarDomain implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 300, 900];
    public int $timeout = 60;

    public function __construct(public int $subscriptionDomainId)
    {
    }

    public function handle(DomainRegistrarManager $manager, AdminNotificationService $notifications): void
    {
        $domain = SubscriptionDomain::find($this->subscriptionDomainId);
        if (! $domain || ! $domain->registrar_account_id) {
            return;
        }

        $account = RegistrarAccount::find($domain->registrar_account_id);
        if (! $account || ! $account->is_active) {
            $domain->update(['sync_status' => 'failed', 'sync_error_summary' => 'Akun registrar tidak aktif']);
            return;
        }

        $lock = Cache::lock('registrar:sync:domain:'.$domain->id, 60);
        if (! $lock->get()) {
            return;
        }

        // P1: Idempotency per hari — retry kedua di hari sama tidak violate unique, melainkan refresh operasi yang ada
        $key = 'sync_domain_'.$domain->id.'_'.now()->format('Y-m-d');
        $op = \App\Models\RegistrarOperation::firstOrCreate(
            ['idempotency_key' => $key],
            [
                'registrar_account_id' => $account->id,
                'subscription_domain_id' => $domain->id,
                'operation_type' => 'sync',
                'status' => 'processing',
                'started_at' => now(),
            ]
        );
        if (! $op->wasRecentlyCreated) {
            $op->update(['status' => 'processing', 'started_at' => now(), 'completed_at' => null, 'error_summary' => null, 'response_payload_redacted' => null]);
        }

        try {
            if (! $manager->isEnabled()) {
                $op->update(['status' => 'failed', 'completed_at' => now(), 'error_summary' => 'Integrasi disabled (mode '.$manager->effectiveMode().')']);
                return;
            }
            if (! $manager->canPerform('sync')) {
                $op->update(['status' => 'failed', 'completed_at' => now(), 'error_summary' => 'Sync tidak diizinkan pada mode '.$manager->effectiveMode()]);
                return;
            }

            $provider = $manager->providerFor($account);
            $result = $provider->getDomain($account, $domain->domain_name);

            if (! ($result['success'] ?? false)) {
                $domain->update([
                    'sync_status' => 'failed',
                    'sync_error_summary' => mb_substr($result['message'] ?? 'Sync gagal', 0, 500),
                    'last_synced_at' => now(),
                ]);
                $op->update(['status' => 'failed', 'completed_at' => now(), 'error_summary' => $result['message'] ?? 'Sync gagal', 'response_payload_redacted' => ['message' => $result['message'] ?? '']]);

                // P1-3/4: per-user
                if (str_contains($result['code'] ?? '', 'http_')) {
                    $notifications->notifyAdmins(
                        'domain_sync_failed',
                        "Sync domain {$domain->domain_name} gagal",
                        "Sinkronisasi {$domain->domain_name} via {$account->name} gagal: ".($result['message'] ?? '-'),
                        ['subscription_domain_id' => $domain->id, 'domain_name' => $domain->domain_name, 'registrar_account_id' => $account->id]
                    );
                }
                $account->update(['last_error_at' => now(), 'last_error_summary' => $result['message'] ?? 'Sync gagal']);

                return;
            }

            $data = $result['data'] ?? [];
            // Expect provider returns expiry, status, nameservers etc.
            $expiresAt = $data['expires_at'] ?? $data['expiry'] ?? null;
            $status = $data['status'] ?? $data['provider_status'] ?? null;

            $domain->update([
                'provider_status' => $status,
                'provider_metadata' => $data,
                'sync_status' => 'synced',
                'sync_error_summary' => null,
                'last_synced_at' => now(),
                'not_found_at' => null,
                'expires_at' => $expiresAt ? \Carbon\Carbon::parse($expiresAt)->toDateString() : $domain->expires_at,
            ]);
            $op->update(['status' => 'completed', 'completed_at' => now(), 'response_payload_redacted' => ['status' => $status]]);

            $account->update(['last_synced_at' => now(), 'last_error_at' => null, 'last_error_summary' => null]);
        } catch (\Throwable $e) {
            Log::error('SyncRegistrarDomain failed', ['domain_id' => $domain->id, 'error' => $e->getMessage()]);
            $domain->update(['sync_status' => 'failed', 'sync_error_summary' => mb_substr($e->getMessage(), 0, 500)]);
            if (isset($op)) {
                $op->update(['status' => 'failed', 'completed_at' => now(), 'error_summary' => mb_substr($e->getMessage(), 0, 500)]);
            }
            throw $e; // retry via backoff
        } finally {
            $lock->release();
        }
    }

    public function failed(\Throwable $e): void
    {
        SubscriptionDomain::whereKey($this->subscriptionDomainId)->update([
            'sync_status' => 'failed',
            'sync_error_summary' => mb_substr($e->getMessage(), 0, 500),
        ]);
    }
}
