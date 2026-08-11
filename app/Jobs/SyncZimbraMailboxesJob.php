<?php

namespace App\Jobs;

use App\Models\Mailbox;
use App\Models\SubscriptionMailHosting;
use App\Models\User;
use App\Services\MailServerResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class SyncZimbraMailboxesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 300, 900];

    public function __construct(public int $mailHostingId, public ?int $actorId = null)
    {
    }

    public function handle(MailServerResolver $resolver): void
    {
        $mailHosting = SubscriptionMailHosting::with('mailServer')->findOrFail($this->mailHostingId);
        $result = $resolver->resolve($mailHosting->mailServer)->listAccounts($mailHosting->domain);

        if (! $result['success']) {
            throw new \RuntimeException($result['message'] ?? 'Zimbra tidak dapat mengambil daftar mailbox.');
        }

        $domainSuffix = '@'.strtolower($mailHosting->domain);
        $accounts = collect($result['data'])
            ->filter(fn (array $account) => str_ends_with(strtolower((string) ($account['email'] ?? '')), $domainSuffix))
            ->unique(fn (array $account) => strtolower((string) $account['email']))
            ->values();

        [$imported, $skipped] = DB::transaction(function () use ($accounts, $mailHosting) {
            $imported = 0;
            $skipped = 0;

            foreach ($accounts as $account) {
                $email = strtolower((string) $account['email']);
                $existing = Mailbox::where('email', $email)->lockForUpdate()->first();

                if ($existing) {
                    // Do not reassign an account that belongs to another CRM service.
                    if ($existing->subscription_mail_hosting_id !== $mailHosting->id) {
                        $skipped++;
                        continue;
                    }

                    if (blank($existing->zimbra_id) && filled($account['id'] ?? null)) {
                        $existing->update(['zimbra_id' => $account['id']]);
                    }

                    $skipped++;
                    continue;
                }

                Mailbox::create([
                    'subscription_mail_hosting_id' => $mailHosting->id,
                    'email' => $email,
                    'zimbra_id' => $account['id'] ?? null,
                    'quota_mb' => 0,
                    'alias_count' => 0,
                    'is_active' => true,
                    'managed_by_crm' => false,
                    'provisioning_status' => 'ready',
                    'provisioning_error' => null,
                    'provisioned_at' => now(),
                ]);
                $imported++;
            }

            return [$imported, $skipped];
        });

        activity('mailboxes')
            ->performedOn($mailHosting)
            ->causedBy($this->actorId ? User::find($this->actorId) : null)
            ->withProperties([
                'subject_label' => $mailHosting->domain,
                'event_label' => 'Sinkronisasi mailbox Zimbra',
                'mail_server_id' => $mailHosting->mail_server_id,
                'domain' => $mailHosting->domain,
                'imported' => $imported,
                'skipped' => $skipped,
            ])
            ->log('Sinkronisasi read-only mailbox Zimbra selesai');
    }
}
