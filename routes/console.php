<?php

use App\Jobs\GenerateMonthlyInvoices;
use App\Jobs\MarkOverdueInvoices;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new MarkOverdueInvoices)->dailyAt('01:00');

Schedule::job(new GenerateMonthlyInvoices)->dailyAt('00:05')->when(function () {
    return \App\Models\SystemSetting::get('billing.auto_generate_enabled', false)
        && now()->day === (int) \App\Models\SystemSetting::get('billing.auto_generate_day', 1);
});
