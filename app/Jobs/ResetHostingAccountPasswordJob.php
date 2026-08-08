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

class ResetHostingAccountPasswordJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 300, 900];

    public function __construct(public int $hostingId)
    {
    }

    public function handle(WebHostResolver $resolver): void
    {
        $hosting = SubscriptionHosting::with('hostingServer')->findOrFail($this->hostingId);

        if (! $hosting->managed_by_crm || ! $hosting->remote_user_created_at || $hosting->provisioning_status !== 'ready') {
            throw new \RuntimeException('Akun hosting belum siap atau tidak dikelola CRM.');
        }

        if (! $hosting->password_encrypted) {
            throw new \RuntimeException('Password baru belum disimpan di CRM.');
        }

        $service = $resolver->resolve($hosting->hostingServer);

        if (! $service->changePassword($hosting->username, $hosting->password_encrypted)) {
            throw new \RuntimeException('HestiaCP tidak dapat mengubah password.');
        }

        $hosting->update(['provisioning_error' => null]);
    }

    public function failed(Throwable $exception): void
    {
        SubscriptionHosting::whereKey($this->hostingId)->update([
            'provisioning_error' => 'Reset password belum berhasil disinkronkan ke server HestiaCP.',
        ]);
    }
}
