<?php

use App\Jobs\GenerateMonthlyInvoices;
use App\Jobs\MarkOverdueInvoices;
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
