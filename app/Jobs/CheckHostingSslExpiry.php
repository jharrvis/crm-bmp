<?php

namespace App\Jobs;

use App\Models\SubscriptionHosting;
use App\Models\SystemSetting;
use App\Services\Admin\AdminNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CheckHostingSslExpiry implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 300;

    public function handle(AdminNotificationService $service): void
    {
        $lock = Cache::lock('notifications:ssl-expiry', 600);
        if (! $lock->get()) {
            return;
        }

        try {
            $days = SystemSetting::get('notifications.hosting_ssl_reminder_days', [14, 7, 3, 1]);
            if (! is_array($days)) {
                $days = [14, 7, 3, 1];
            }
            $today = now()->startOfDay();
            $hostings = SubscriptionHosting::with('subscription.client')->whereNotNull('ssl_expiry')->get();
            $sent = 0;
            foreach ($hostings as $hosting) {
                $expires = $hosting->ssl_expiry->copy()->startOfDay();
                $diff = $today->diffInDays($expires, false);
                if ($diff < 0) {
                    continue;
                }
                if (! in_array($diff, $days, true)) {
                    continue;
                }
                $service->notifyAdmins(
                    "hosting_ssl_expiry_{$diff}",
                    "SSL {$hosting->domain} akan expired dalam {$diff} hari",
                    "SSL domain {$hosting->domain} ({$hosting->username}) akan expired {$expires->format('d M Y')}.",
                    [
                        'subscription_id' => $hosting->subscription_id,
                        'subscription_hosting_id' => $hosting->id,
                        'domain' => $hosting->domain,
                        'expires_at' => $expires->toDateString(),
                        'days_left' => $diff,
                    ]
                );
                $sent++;
            }
            if ($sent) {
                Log::info("CheckHostingSslExpiry: {$sent} notifications.");
            }
        } finally {
            $lock->release();
        }
    }
}
