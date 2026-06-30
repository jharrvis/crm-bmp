<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->boolean('is_prorated')->default(false)->after('total');
            $table->date('proration_start_date')->nullable()->after('is_prorated');
            $table->date('proration_end_date')->nullable()->after('proration_start_date');
            $table->integer('proration_days')->nullable()->after('proration_end_date');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn([
                'is_prorated',
                'proration_start_date',
                'proration_end_date',
                'proration_days',
            ]);
        });
    }
};
