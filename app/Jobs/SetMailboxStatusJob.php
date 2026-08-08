<?php

namespace App\Jobs;

use App\Models\Mailbox;
use App\Services\MailServerResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SetMailboxStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 300, 900];

    public function __construct(public int $mailboxId, public bool $activate, public bool $bySubscription = false)
    {
    }

    public function handle(MailServerResolver $resolver): void
    {
        $mailbox = Mailbox::with('mailHosting.mailServer')->findOrFail($this->mailboxId);
        $service = $resolver->resolve($mailbox->mailHosting->mailServer);
        $success = $this->activate ? $service->activate($mailbox->email) : $service->suspend($mailbox->email);

        if (! $success) {
            throw new \RuntimeException('Zimbra tidak dapat mengubah status mailbox.');
        }

        $mailbox->update([
            'is_active' => $this->activate,
            'suspended_by_subscription' => $this->bySubscription && ! $this->activate,
            'provisioning_error' => null,
        ]);
    }
}
