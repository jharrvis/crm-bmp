<?php

namespace App\Jobs;

use App\Models\SubscriptionMailHosting;
use App\Models\User;
use App\Services\ZimbraMailboxSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncZimbraMailboxesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 300, 900];

    public function __construct(public int $mailHostingId, public ?int $actorId = null)
    {
    }

    public function handle(ZimbraMailboxSyncService $syncService): void
    {
        $mailHosting = SubscriptionMailHosting::with('mailServer')->findOrFail($this->mailHostingId);
        $summary = $syncService->sync($mailHosting);

        activity('mailboxes')
            ->performedOn($mailHosting)
            ->causedBy($this->actorId ? User::find($this->actorId) : null)
            ->withProperties([
                'subject_label' => $mailHosting->domain,
                'event_label' => 'Sinkronisasi mailbox Zimbra',
                'mail_server_id' => $mailHosting->mail_server_id,
                'domain' => $mailHosting->domain,
                'imported' => $summary['imported'],
                'updated' => $summary['updated'],
                'skipped' => $summary['skipped'],
                'total_remote' => $summary['total'],
            ])
            ->log('Sinkronisasi read-only mailbox Zimbra selesai');
    }
}
