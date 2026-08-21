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
            // Domain Registrar — integrations (non-secret, mode & timeout di config, ambang notifikasi di sini)
            [
                'group' => 'notifications',
                'key' => 'notifications.domain_reminder_days',
                'value' => '[30,14,7,3,1]',
                'type' => 'json',
                'description' => 'Hari sebelum expiry domain untuk notifikasi admin',
            ],
            [
                'group' => 'notifications',
                'key' => 'notifications.domain_channel',
                'value' => 'database',
                'type' => 'string',
                'description' => 'Channel notifikasi domain: database, email, both',
            ],
            [
                'group' => 'notifications',
                'key' => 'notifications.hosting_ssl_reminder_days',
                'value' => '[14,7,3,1]',
                'type' => 'json',
                'description' => 'Hari sebelum expiry SSL hosting untuk notifikasi',
            ],
            [
                'group' => 'notifications',
                'key' => 'notifications.retention_days',
                'value' => '90',
                'type' => 'integer',
                'description' => 'Retensi notifikasi admin (hari), auto-prune setelahnya',
            ],
            // Domain Registrar — mode & operasional (P2-9)
            [
                'group' => 'domain_registrar',
                'key' => 'domain_registrar.mode',
                'value' => 'read_only',
                'type' => 'string',
                'description' => 'Mode operasi registrar: disabled, read_only, managed',
            ],
            [
                'group' => 'domain_registrar',
                'key' => 'domain_registrar.sync_interval_hours',
                'value' => '24',
                'type' => 'integer',
                'description' => 'Interval sinkronisasi per akun (jam)',
            ],
            [
                'group' => 'domain_registrar',
                'key' => 'domain_registrar.timeout',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Timeout API registrar (detik)',
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
