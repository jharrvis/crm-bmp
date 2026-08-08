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

class DeleteHostingAccountJob implements ShouldQueue
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

        if (! $hosting->managed_by_crm || ! $hosting->remote_user_created_at || $hosting->provisioning_status !== 'deleting') {
            throw new \RuntimeException('Akun belum memenuhi syarat untuk dihapus dari HestiaCP.');
        }

        if ($hosting->username === 'admin') {
            throw new \RuntimeException('User admin sistem tidak boleh dihapus.');
        }

        $service = $resolver->resolve($hosting->hostingServer);

        $existingUser = $service->findUser($hosting->username);

        if (! $existingUser['success']) {
            throw new \RuntimeException('Status user HestiaCP tidak dapat diverifikasi sebelum penghapusan.');
        }

        // A previous attempt may have completed remotely before the worker stopped.
        if ($existingUser['data'] !== null && ! $service->deleteUser($hosting->username)) {
            throw new \RuntimeException('HestiaCP tidak dapat menghapus user.');
        }

        $hosting->delete();
    }

    public function failed(Throwable $exception): void
    {
        SubscriptionHosting::whereKey($this->hostingId)->update([
            'provisioning_status' => 'delete_failed',
            'provisioning_error' => 'User belum dapat dihapus dari server HestiaCP.',
        ]);
    }
}
