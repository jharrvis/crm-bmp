<?php

namespace App\Jobs;

use App\Models\Mailbox;
use App\Services\MailServerResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProvisionMailboxJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 300, 900];

    public function __construct(public int $mailboxId)
    {
    }

    public function handle(MailServerResolver $resolver): void
    {
        $mailbox = Mailbox::with('mailHosting.mailServer')->findOrFail($this->mailboxId);

        if ($mailbox->provisioning_status === 'ready') {
            return;
        }

        if (! $mailbox->managed_by_crm) {
            return;
        }

        $mailHosting = $mailbox->mailHosting;

        if ($mailHosting->mailServer?->type === 'zimbra') {
            $mailbox->update([
                'provisioning_status' => 'failed',
                'provisioning_error' => 'CRM untuk Zimbra bersifat read-only. Buat mailbox dari panel Zimbra.',
            ]);

            return;
        }

        if ($mailHosting->status !== 'active') {
            return;
        }

        if ($mailHosting->provisioning_status !== 'ready') {
            $this->release(60);

            return;
        }

        $result = $resolver->resolve($mailHosting->mailServer)->createAccount(
            $mailbox->email,
            $mailbox->password_encrypted,
            array_filter([
                'zimbraMailQuota' => $mailbox->quota_mb > 0 ? $mailbox->quota_mb * 1024 * 1024 : null,
                'displayName' => $mailbox->display_name,
            ])
        );

        if (! $result['success']) {
            throw new \RuntimeException($result['message'] ?? 'Zimbra tidak dapat membuat mailbox.');
        }

        $mailbox->update([
            'zimbra_id' => $result['id'],
            'is_active' => true,
            'provisioning_status' => 'ready',
            'provisioning_error' => null,
            'provisioned_at' => now(),
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Mailbox::whereKey($this->mailboxId)->update([
            'provisioning_status' => 'failed',
            'provisioning_error' => 'Mailbox belum dapat diprovisikan. Periksa koneksi server mail.',
        ]);
    }
}
