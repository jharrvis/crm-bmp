<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fase 2: DNS SRS-X hanya untuk domain yang secara eksplisit memakai managed DNS.
        if (! Schema::hasColumn('subscription_domains', 'managed_dns_enabled')) {
            Schema::table('subscription_domains', function (Blueprint $table) {
                $table->boolean('managed_dns_enabled')->default(false)->after('domain_account_mode')
                    ->comment('true jika domain ini eksplisit memakai managed DNS SRS-X (opsi DNS hanya aktif bila true)');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('subscription_domains', 'managed_dns_enabled')) {
            Schema::table('subscription_domains', function (Blueprint $table) {
                $table->dropColumn('managed_dns_enabled');
            });
        }
    }
};