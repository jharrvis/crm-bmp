<?php

use App\Jobs\CheckDomainExpiry;
use App\Jobs\CheckHostingSslExpiry;
use App\Jobs\CheckRegistrarHealth;
use App\Jobs\GenerateMonthlyInvoices;
use App\Jobs\MarkOverdueInvoices;
use App\Jobs\RefreshHestiaServerSnapshotJob;
use App\Jobs\SendInvoiceReminders;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new MarkOverdueInvoices)->dailyAt('01:00');
Schedule::job(new SendInvoiceReminders)->dailyAt('08:00');

Schedule::job(new GenerateMonthlyInvoices)->dailyAt('00:05')->when(function () {
    return \App\Models\SystemSetting::get('billing.auto_generate_enabled', false)
        && now()->day === (int) \App\Models\SystemSetting::get('billing.auto_generate_day', 1);
});

Schedule::call(function () {
    \App\Models\HostingServer::where('is_active', true)->where('type', 'hestiacp')
        ->each(fn ($server) => RefreshHestiaServerSnapshotJob::dispatch($server->id));
})->dailyAt('04:00');

Schedule::job(new CheckDomainExpiry)->dailyAt('07:00');
Schedule::job(new CheckHostingSslExpiry)->dailyAt('07:15');
Schedule::job(new CheckRegistrarHealth)->hourly()->when(fn () => app(\App\DomainRegistrars\DomainRegistrarManager::class)->isEnabled());

Schedule::command('registrar:recover-stale-operations')->everyFiveMinutes();

// P1: Sync otomatis dimatikan sampai capability listDomains tervalidasi UAT — hindari loop gagal tiap jam
// Manual import via UI tetap tersedia (registrar-accounts/{id}/import-manual)
Schedule::call(function () {
    $manager = app(\App\DomainRegistrars\DomainRegistrarManager::class);
    if (! $manager->isEnabled() || ! $manager->canPerform('sync')) {
        return;
    }
    // Jika capability listDomains false, jangan dispatch otomatis
    try {
        $sample = \App\Models\RegistrarAccount::where('is_active', true)->first();
        if ($sample) {
            $caps = $manager->providerFor($sample)->capabilities();
            if (! $caps->listDomains) {
                return;
            }
        }
    } catch (\Throwable $e) {
        return;
    }
    $intervalHours = (int) \App\Models\SystemSetting::get('domain_registrar.sync_interval_hours', 24);
    $intervalHours = max(1, min(720, $intervalHours)); // clamp 1-720 jam
    $accounts = \App\Models\RegistrarAccount::where('is_active', true)->get();
    foreach ($accounts as $account) {
        if ($account->last_synced_at && $account->last_synced_at->gt(now()->subHours($intervalHours - 1))) {
            continue;
        }
        // Hindari spam notifikasi gagal berulang: jika last_error adalah not_validated dalam 24 jam, skip
        if ($account->last_error_summary && str_contains($account->last_error_summary, 'belum tervalidasi') && $account->last_error_at && $account->last_error_at->gt(now()->subDay())) {
            continue;
        }
        \App\Jobs\SyncRegistrarAccountDomains::dispatch($account->id, false)->afterCommit();
    }
})->hourly();

// Prune old notifications (retention 90 days default)
Schedule::call(function () {
    $days = (int) \App\Models\SystemSetting::get('notifications.retention_days', 90);
    app(\App\Services\Admin\AdminNotificationService::class)->pruneExpired($days);
})->dailyAt('02:00');
