<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscription_connectivities', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropColumn(['vendor_id', 'metro_cid', 'metro_ip_address', 'metro_bandwidth']);
            $table->foreignId('metro_ethernet_id')->nullable()->constrained('metro_ethernets')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_connectivities', function (Blueprint $table) {
            $table->dropForeign(['metro_ethernet_id']);
            $table->dropColumn('metro_ethernet_id');

            // Re-add dropped columns
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->string('metro_cid')->nullable();
            $table->string('metro_ip_address')->nullable();
            $table->integer('metro_bandwidth')->nullable();
        });
    }
};
