<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FixInternetBackupAmpersandSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('internet_backups')
            ->where('name', 'like', '%&amp;%')
            ->update(['name' => DB::raw("REPLACE(name, '&amp;', '&')")]);

        $this->command->info('Fixed &amp; in internet_backups name column.');
    }
}
