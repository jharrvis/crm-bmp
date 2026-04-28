<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscription_connectivities', function (Blueprint $table) {
            $table->string('zabbix_group_id')->nullable()->after('signal_rx');
            $table->string('zabbix_group_name')->nullable()->after('zabbix_group_id');
            $table->string('zabbix_host_id')->nullable()->after('zabbix_group_name');
            $table->string('zabbix_host_name')->nullable()->after('zabbix_host_id');
            $table->json('zabbix_interfaces')->nullable()->after('zabbix_host_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_connectivities', function (Blueprint $table) {
            $table->dropColumn([
                'zabbix_group_id',
                'zabbix_group_name',
                'zabbix_host_id',
                'zabbix_host_name',
                'zabbix_interfaces',
            ]);
        });
    }
};
