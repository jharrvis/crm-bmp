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

class SyncRegistrarAccountDomains implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 300;

    public function __construct(public int $registrarAccountId, public bool $dryRun = true)
    {
    }

    public function handle(DomainRegistrarManager $manager, AdminNotificationService $notifications): void
    {
        $account = RegistrarAccount::findOrFail($this->registrarAccountId);

        if (! $account->is_active) {
            throw new \RuntimeException('Akun registrar tidak aktif.');
        }

        if (! $manager->isEnabled()) {
            Log::info('SyncRegistrarAccountDomains skipped — mode disabled', ['account_id' => $account->id, 'mode' => $manager->effectiveMode()]);
            return;
        }
        if (! $manager->canPerform('sync')) {
            Log::info('SyncRegistrarAccountDomains skipped — mode not allowed', ['account_id' => $account->id, 'mode' => $manager->effectiveMode()]);
            return;
        }

        $lock = Cache::lock('registrar:sync:account:'.$account->id, 300);
        if (! $lock->get()) {
            return;
        }

        try {
            $provider = $manager->providerFor($account);
            // P1: capability listDomains false sampai UAT — jangan call endpoint, hindari loop gagal tiap jam
            if (! $provider->capabilities()->listDomains) {
                Log::info('registrar: listDomains capability disabled — skip sync, gunakan manual import', ['account_id' => $account->id]);
                return;
            }
            $result = $provider->listDomains($account, ['limit' => 200]);

            if (! ($result['success'] ?? false)) {
                // P1-6: listDomains belum tervalidasi — jangan mark manual_review, cukup log
                $isNotValidated = ($result['code'] ?? null) === 'not_validated';
                $account->update(['last_error_at' => now(), 'last_error_summary' => $result['message'] ?? 'List domain gagal']);
                $notifications->notifyAdmins('domain_sync_failed', "Import domain {$account->name} gagal", $result['message'] ?? 'Gagal', ['registrar_account_id' => $account->id]);
                if ($isNotValidated) {
                    Log::warning('registrar: listDomains not validated — staging manual required', ['account_id' => $account->id]);
                    return;
                }
                throw new \RuntimeException($result['message'] ?? 'Gagal list domain');
            }

            $remote = collect($result['data'] ?? []);
            // Normalize to domain_name key
            $remoteDomains = $remote->map(fn ($item) => is_string($item) ? strtolower($item) : strtolower($item['domain'] ?? $item['domain_name'] ?? ''))->filter()->values();

            // Detect conflicts: domain exists in another account
            $conflicts = [];
            foreach ($remoteDomains as $name) {
                $existing = SubscriptionDomain::whereRaw('LOWER(domain_name) = ?', [$name])->first();
                if ($existing && $existing->registrar_account_id && $existing->registrar_account_id !== $account->id) {
                    $conflicts[] = $name;
                    Log::warning('registrar: domain conflict', ['domain' => $name, 'existing_account' => $existing->registrar_account_id, 'new_account' => $account->id]);
                    $notifications->notifyAdmins('domain_conflict', "Konflik domain {$name}", "Domain {$name} ditemukan di akun {$account->name} tapi sudah tertaut ke akun lain. Perlu resolve manual.", ['domain_name' => $name, 'registrar_account_id' => $account->id]);
                }
            }

            if ($this->dryRun) {
                Log::info('SyncRegistrarAccountDomains dry-run', ['account_id' => $account->id, 'total' => $remoteDomains->count(), 'conflicts' => count($conflicts)]);
                // Fase 1: staging/review belum ada tabel staging — dry-run hanya log; operator review manual via log/notifikasi
            } else {
                // Real sync: update last_synced_at for matched domains
                foreach ($remoteDomains as $name) {
                    $local = SubscriptionDomain::whereRaw('LOWER(domain_name) = ?', [$name])->where('registrar_account_id', $account->id)->first();
                    if ($local) {
                        SyncRegistrarDomain::dispatch($local->id)->afterCommit();
                    }
                }
                // P1-6: Jangan tandai manual_review bila hasil mungkin ter-paginasi/truncated
                $isTruncated = $remoteDomains->count() >= 200 || isset($result['next_cursor']);
                if ($isTruncated) {
                    Log::warning('registrar: sync truncated — skip manual_review marking', ['account_id' => $account->id, 'count' => $remoteDomains->count()]);
                } else {
                    SubscriptionDomain::where('registrar_account_id', $account->id)
                        ->whereNotIn('domain_name', $remoteDomains->toArray())
                        ->update(['sync_status' => 'manual_review', 'sync_error_summary' => 'Domain tidak ditemukan di provider saat sync terakhir', 'not_found_at' => now()]);
                }
            }

            $account->update(['last_synced_at' => now(), 'last_error_at' => null, 'last_error_summary' => null]);
        } finally {
            $lock->release();
        }
    }
}
