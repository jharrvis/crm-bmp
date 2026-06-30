<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'group' => 'billing',
                'key' => 'billing.ppn_rate',
                'value' => '11',
                'type' => 'float',
                'description' => 'Tarif PPN (%)',
            ],
            [
                'group' => 'billing',
                'key' => 'billing.pph23_rate',
                'value' => '2',
                'type' => 'float',
                'description' => 'Tarif PPh23 (%)',
            ],
            [
                'group' => 'billing',
                'key' => 'billing.default_due_days',
                'value' => '7',
                'type' => 'integer',
                'description' => 'Hari jatuh tempo dari tanggal generate',
            ],
            [
                'group' => 'billing',
                'key' => 'billing.auto_generate_day',
                'value' => '1',
                'type' => 'integer',
                'description' => 'Tanggal auto-generate invoice setiap bulan (1-28)',
            ],
            [
                'group' => 'billing',
                'key' => 'billing.auto_generate_enabled',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Aktifkan auto-generate invoice bulanan',
            ],
            [
                'group' => 'billing',
                'key' => 'billing.proration_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Aktifkan penghitungan prorata',
            ],
            [
                'group' => 'billing',
                'key' => 'billing.reminder_days_before',
                'value' => '[7,3,1]',
                'type' => 'json',
                'description' => 'Hari reminder sebelum jatuh tempo',
            ],
            [
                'group' => 'billing',
                'key' => 'billing.reminder_days_after',
                'value' => '[1,7,14]',
                'type' => 'json',
                'description' => 'Hari reminder setelah overdue',
            ],
            [
                'group' => 'billing',
                'key' => 'billing.reminder_channel',
                'value' => 'email',
                'type' => 'string',
                'description' => 'Channel reminder: email, whatsapp, both',
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
