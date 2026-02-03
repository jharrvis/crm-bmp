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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->decimal('custom_price', 15, 2)->nullable()->after('price_at_subscription')
                ->comment('Harga negosiasi. Jika null, pakai harga paket.');
            $table->integer('billing_period_months')->default(1)->after('custom_price')
                ->comment('Durasi siklus tagihan: 1=bulanan, 3=triwulan, 12=tahunan');
            $table->decimal('discount_percent', 5, 2)->nullable()->after('billing_period_months')
                ->comment('Diskon dalam persen');
            $table->text('discount_notes')->nullable()->after('discount_percent')
                ->comment('Alasan diskon/negosiasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['custom_price', 'billing_period_months', 'discount_percent', 'discount_notes']);
        });
    }
};
