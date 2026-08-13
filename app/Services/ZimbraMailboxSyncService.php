<?php

namespace App\Services;

use App\Models\Mailbox;
use App\Models\SubscriptionMailHosting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ZimbraMailboxSyncService
{
    /**
     * Read remote mailbox metadata and mirror it locally without changing Zimbra.
     * Existing CRM records keep their ownership and are never reassigned.
     *
     * @return array{imported: int, updated: int, skipped: int, total: int, locked: bool}
     */
    public function sync(SubscriptionMailHosting $mailHosting): array
    {
        $mailHosting->loadMissing('mailServer');

        if (! $mailHosting->mailServer) {
            throw new \RuntimeException('Server mail hosting tidak ditemukan.');
        }

        $lock = Cache::lock('zimbra-mailbox-sync:'.$mailHosting->id, 45);

        if (! $lock->get()) {
            return ['imported' => 0, 'updated' => 0, 'skipped' => 0, 'total' => 0, 'locked' => true];
        }

        try {
            $result = app(MailServerResolver::class)
                ->resolve($mailHosting->mailServer)
                ->listAccounts($mailHosting->domain);

            if (! ($result['success'] ?? false)) {
                throw new \RuntimeException($result['message'] ?? 'Zimbra tidak dapat mengambil daftar mailbox.');
            }

            $domainSuffix = '@'.strtolower($mailHosting->domain);
            $accounts = collect($result['data'] ?? [])
                ->filter(fn (array $account) => str_ends_with(strtolower((string) ($account['email'] ?? '')), $domainSuffix))
                ->unique(fn (array $account) => strtolower((string) ($account['email'] ?? '')))
                ->values();

            [$imported, $updated, $skipped] = DB::transaction(function () use ($accounts, $mailHosting) {
                $imported = 0;
                $updated = 0;
                $skipped = 0;

                foreach ($accounts as $account) {
                    $email = strtolower((string) $account['email']);
                    $existing = Mailbox::where('email', $email)->lockForUpdate()->first();

                    if ($existing && $existing->subscription_mail_hosting_id !== $mailHosting->id) {
                        // Never reassign an account from another CRM mail-hosting service.
                        $skipped++;
                        continue;
                    }

                    $metadata = [];

                    if ($account['has_display_name'] ?? false) {
                        $metadata['display_name'] = filled($account['display_name'] ?? null) ? $account['display_name'] : null;
                    }

                    if ($account['has_quota'] ?? false) {
                        $metadata['quota_mb'] = max(0, (int) ($account['quota_mb'] ?? 0));
                    }

                    if ($account['has_used_quota'] ?? false) {
                        $metadata['used_quota_mb'] = max(0, (int) ($account['used_quota_mb'] ?? 0));
                    }

                    if ($account['has_status'] ?? false) {
                        $remoteStatus = $this->normalizeStatus($account['status'] ?? null);
                        $metadata['is_active'] = $remoteStatus === 'active';
                        $metadata['remote_status'] = $remoteStatus;
                    }

                    if (! $existing) {
                        $metadata = array_replace([
                            'display_name' => null,
                            'quota_mb' => 0,
                            'used_quota_mb' => null,
                            'is_active' => true,
                            'remote_status' => 'unknown',
                        ], $metadata);

                        DB::table('mailboxes')->insert([
                            'subscription_mail_hosting_id' => $mailHosting->id,
                            'email' => $email,
                            'zimbra_id' => filled($account['id'] ?? null) ? $account['id'] : null,
                            'display_name' => $metadata['display_name'],
                            'quota_mb' => $metadata['quota_mb'],
                            'used_quota_mb' => $metadata['used_quota_mb'],
                            'alias_count' => 0,
                            'is_active' => $metadata['is_active'],
                            'managed_by_crm' => false,
                            'remote_status' => $metadata['remote_status'],
                            'provisioning_status' => 'ready',
                            'provisioning_error' => null,
                            'provisioned_at' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $imported++;
                        continue;
                    }

                    if (blank($existing->zimbra_id) && filled($account['id'] ?? null)) {
                        $metadata['zimbra_id'] = $account['id'];
                    }

                    $changes = collect($metadata)
                        ->filter(fn ($value, string $key) => $existing->{$key} !== $value)
                        ->all();

                    if ($changes !== []) {
                        $changes['updated_at'] = now();
                        DB::table('mailboxes')->where('id', $existing->id)->update($changes);
                        $updated++;
                    }
                }

                DB::table('subscription_mail_hostings')->where('id', $mailHosting->id)->update([
                    'mailboxes_last_synced_at' => now(),
                    'mailboxes_sync_error' => null,
                    'updated_at' => now(),
                ]);

                return [$imported, $updated, $skipped];
            });

            return [
                'imported' => $imported,
                'updated' => $updated,
                'skipped' => $skipped,
                'total' => $accounts->count(),
                'locked' => false,
            ];
        } catch (\Throwable $exception) {
            DB::table('subscription_mail_hostings')->where('id', $mailHosting->id)->update([
                'mailboxes_sync_error' => 'Sinkronisasi mailbox dari Zimbra gagal. Data lokal terakhir tetap digunakan.',
                'updated_at' => now(),
            ]);

            throw $exception;
        } finally {
            $lock->release();
        }
    }

    private function normalizeStatus(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'active' => 'active',
            'maintenance' => 'maintenance',
            'locked' => 'locked',
            'closed' => 'closed',
            'lockout' => 'lockout',
            default => 'unknown',
        };
    }
}
