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
            $table->boolean('uses_pph23')->default(false)->after('ppn_amount');
            $table->decimal('pph23_amount', 15, 2)->nullable()->after('uses_pph23');
        });

        DB::table('subscriptions')
            ->whereNull('pph23_amount')
            ->update([
                'uses_pph23' => false,
            ]);
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['uses_pph23', 'pph23_amount']);
        });
    }
};
