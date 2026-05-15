<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->boolean('uses_ppn')->default(false)->after('billing_period_months');
            $table->decimal('ppn_amount', 15, 2)->nullable()->after('uses_ppn');
        });

        DB::table('subscriptions')
            ->whereNull('ppn_amount')
            ->update([
                'uses_ppn' => false,
            ]);
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['uses_ppn', 'ppn_amount']);
        });
    }
};
