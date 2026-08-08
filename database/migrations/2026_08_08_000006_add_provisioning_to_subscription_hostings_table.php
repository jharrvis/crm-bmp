<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $subDupes = DB::table('subscription_hostings')
            ->select('subscription_id')
            ->groupBy('subscription_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        if ($subDupes > 0) {
            throw new RuntimeException('Migration dihentikan: ditemukan duplikat subscription_hostings per subscription. Perbaiki data legacy sebelum melanjutkan.');
        }

        $serverDupes = DB::table('subscription_hostings')
            ->whereNotNull('username')
            ->select('hosting_server_id', 'username')
            ->groupBy('hosting_server_id', 'username')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        if ($serverDupes > 0) {
            throw new RuntimeException('Migration dihentikan: ditemukan duplikat subscription_hostings (hosting_server_id, username). Perbaiki data legacy sebelum melanjutkan.');
        }

        Schema::table('subscription_hostings', function (Blueprint $table) {
            $table->string('provisioning_status')->default('pending')->after('ssl_expiry');
            $table->text('provisioning_error')->nullable()->after('provisioning_status');
            $table->timestamp('provisioned_at')->nullable()->after('provisioning_error');
            $table->boolean('managed_by_crm')->default(true)->after('provisioned_at');
            $table->boolean('suspended_by_subscription')->default(false)->after('managed_by_crm');
            $table->string('hestia_package')->nullable()->after('suspended_by_subscription');

            $table->unique('subscription_id', 'subscription_hostings_subscription_unique');
            $table->unique(['hosting_server_id', 'username'], 'subscription_hostings_server_username_unique');
            $table->index(['hosting_server_id', 'provisioning_status'], 'subscription_hostings_server_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_hostings', function (Blueprint $table) {
            $table->dropIndex('subscription_hostings_server_status_idx');
            $table->dropUnique('subscription_hostings_server_username_unique');
            $table->dropUnique('subscription_hostings_subscription_unique');
            $table->dropColumn([
                'provisioning_status',
                'provisioning_error',
                'provisioned_at',
                'managed_by_crm',
                'suspended_by_subscription',
                'hestia_package',
            ]);
        });
    }
};