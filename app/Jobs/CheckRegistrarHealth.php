<?php

namespace App\Jobs;

use App\DomainRegistrars\DomainRegistrarManager;
use App\Models\RegistrarAccount;
use App\Services\Admin\AdminNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CheckRegistrarHealth implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 120;

    public function handle(DomainRegistrarManager $manager, AdminNotificationService $notifications): void
    {
        // P1-7: kill-switch harus menghentikan call provider
        if (! config('domain-registrars.enabled')) {
            return;
        }

        $lock = Cache::lock('notifications:registrar-health', 3600);
        if (! $lock->get()) {
            return;
        }

        try {
            $accounts = RegistrarAccount::where('is_active', true)->get();
            foreach ($accounts as $account) {
                // Skip if last tested recently and not failed
                if ($account->last_tested_at && $account->last_tested_at->gt(now()->subHour()) && ! $account->last_error_at) {
                    continue;
                }
                try {
                    $provider = $manager->providerFor($account);
                    $result = $provider->testConnection($account);
                    $account->update([
                        'last_tested_at' => now(),
                        'last_error_at' => ($result['success'] ?? false) ? null : now(),
                        'last_error_summary' => ($result['success'] ?? false) ? null : ($result['message'] ?? 'Test koneksi gagal'),
                    ]);
                    if (! ($result['success'] ?? false)) {
                        $notifications->notifyAdmins(
                            'registrar_offline',
                            "Registrar {$account->name} offline",
                            "Test koneksi {$account->name} ({$account->provider}) gagal: ".($result['message'] ?? 'unknown'),
                            ['registrar_account_id' => $account->id, 'provider' => $account->provider]
                        );
                    }
                } catch (\Throwable $e) {
                    Log::warning('CheckRegistrarHealth failed', ['account_id' => $account->id, 'error' => $e->getMessage()]);
                }
            }
        } finally {
            $lock->release();
        }
    }
}
