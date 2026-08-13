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

class DeleteMailboxJob implements ShouldQueue
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

        if (! $mailbox->managed_by_crm) {
            throw new \RuntimeException('Mailbox hasil sinkronisasi tidak dapat dihapus oleh CRM.');
        }

        if ($mailbox->mailHosting->mailServer?->type === 'zimbra') {
            $mailbox->update([
                'provisioning_status' => 'ready',
                'provisioning_error' => 'Penghapusan dibatalkan: CRM untuk Zimbra bersifat read-only.',
            ]);

            return;
        }

        if ($mailbox->mailHosting->status !== 'active') {
            return;
        }

        if (! $resolver->resolve($mailbox->mailHosting->mailServer)->deleteAccount($mailbox->email)) {
            throw new \RuntimeException('Zimbra tidak dapat menghapus mailbox.');
        }

        $mailbox->delete();
    }

    public function failed(Throwable $exception): void
    {
        Mailbox::whereKey($this->mailboxId)->update([
            'provisioning_status' => 'delete_failed',
            'provisioning_error' => 'Mailbox belum dapat dihapus dari server mail.',
        ]);
    }
}
