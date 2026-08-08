<?php

namespace App\Jobs;

use App\Models\SubscriptionHosting;
use App\Services\WebHostResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SetHostingAccountStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 300, 900];

    public function __construct(public int $hostingId, public bool $activate, public bool $bySubscription = false)
    {
    }

    public function handle(WebHostResolver $resolver): void
    {
        $hosting = SubscriptionHosting::with('hostingServer')->findOrFail($this->hostingId);

        if (! $hosting->managed_by_crm || ! $hosting->remote_user_created_at || $hosting->provisioning_status !== 'ready') {
            throw new \RuntimeException('Akun hosting belum siap atau tidak dikelola CRM.');
        }

        $service = $resolver->resolve($hosting->hostingServer);
        $success = $this->activate
            ? $service->unsuspendUser($hosting->username)
            : $service->suspendUser($hosting->username);

        if (! $success) {
            throw new \RuntimeException('HestiaCP tidak dapat mengubah status akun.');
        }

        $hosting->update([
            'suspended_by_subscription' => $this->bySubscription && ! $this->activate,
            'provisioning_error' => null,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        SubscriptionHosting::whereKey($this->hostingId)->update([
            'provisioning_error' => 'Perubahan status akun belum dapat disinkronkan ke server HestiaCP.',
        ]);
    }
}
