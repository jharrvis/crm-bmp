<?php

namespace App\Jobs;

use App\Models\SubscriptionMailHosting;
use App\Services\MailServerResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class EnsureMailDomainJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 300, 900];

    public function __construct(public int $mailHostingId)
    {
    }

    public function handle(MailServerResolver $resolver): void
    {
        $mailHosting = SubscriptionMailHosting::with('mailServer')->findOrFail($this->mailHostingId);

        if ($mailHosting->provisioning_status === 'ready') {
            return;
        }

        if (! $resolver->resolve($mailHosting->mailServer)->ensureDomain($mailHosting->domain)) {
            throw new \RuntimeException('Zimbra tidak dapat menyiapkan domain.');
        }

        $mailHosting->update([
            'provisioning_status' => 'ready',
            'provisioning_error' => null,
            'provisioned_at' => now(),
        ]);
    }

    public function failed(Throwable $exception): void
    {
        SubscriptionMailHosting::whereKey($this->mailHostingId)->update([
            'provisioning_status' => 'failed',
            'provisioning_error' => 'Domain belum dapat diprovisikan. Periksa koneksi dan kredensial server mail.',
        ]);
    }
}
