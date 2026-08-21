<?php

namespace App\Jobs;

use App\Models\AdminNotification;
use App\Models\SubscriptionDomain;
use App\Models\SystemSetting;
use App\Services\Admin\AdminNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CheckDomainExpiry implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 300;

    public function handle(AdminNotificationService $service): void
    {
        $lock = Cache::lock('notifications:domain-expiry', 600);
        if (! $lock->get()) {
            return;
        }

        try {
            $days = SystemSetting::get('notifications.domain_reminder_days', [30, 14, 7, 3, 1]);
            if (! is_array($days)) {
                $days = [30, 14, 7, 3, 1];
            }
            $today = now()->startOfDay();

            $domains = SubscriptionDomain::with('subscription.client', 'registrarAccount')
                ->whereNotNull('expires_at')
                ->get();

            $sent = 0;
            foreach ($domains as $domain) {
                $expires = $domain->expires_at->copy()->startOfDay();
                $diff = $today->diffInDays($expires, false); // positive if future

                if ($diff < 0) {
                    // Overdue — notify once per day
                    if (! in_array(-1, $days, true) && $diff !== 0) {
                        // For overdue, we treat type domain_overdue, not days check
                    }
                    $type = 'domain_overdue';
                    $title = "Domain {$domain->domain_name} telah lewat expiry";
                    $message = "Domain {$domain->domain_name} expired pada {$expires->format('d M Y')} (".abs($diff)." hari lalu). Segera ajukan perpanjangan.";
                    $payload = [
                        'subscription_id' => $domain->subscription_id,
                        'subscription_domain_id' => $domain->id,
                        'registrar_account_id' => $domain->registrar_account_id,
                        'domain_name' => $domain->domain_name,
                        'expires_at' => $expires->toDateString(),
                        'days_left' => $diff,
                    ];
                    // P1-3/4: per-user untuk Owner+Admin
                    $service->notifyAdmins($type, $title, $message, $payload);
                    $sent++;
                    continue;
                }

                if (! in_array($diff, $days, true)) {
                    continue;
                }

                $type = "domain_expiry_{$diff}";
                $title = "Domain {$domain->domain_name} akan expired dalam {$diff} hari";
                $message = "Domain {$domain->domain_name} (".($domain->registrarAccount->name ?? $domain->registrar ?? '-').") akan expired pada {$expires->format('d M Y')}.";
                $payload = [
                    'subscription_id' => $domain->subscription_id,
                    'subscription_domain_id' => $domain->id,
                    'registrar_account_id' => $domain->registrar_account_id,
                    'domain_name' => $domain->domain_name,
                    'expires_at' => $expires->toDateString(),
                    'days_left' => $diff,
                ];

                $service->notifyAdmins($type, $title, $message, $payload);
                $sent++;
            }

            if ($sent > 0) {
                Log::info("CheckDomainExpiry: created {$sent} notifications.");
            }
        } finally {
            $lock->release();
        }
    }
}
