<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('subtotal_amount', 12, 2)->nullable()->after('due_date');
            $table->boolean('uses_tax')->default(false)->after('subtotal_amount');
            $table->decimal('tax_rate', 5, 2)->nullable()->after('uses_tax');
            $table->decimal('tax_amount', 12, 2)->nullable()->after('tax_rate');
            $table->decimal('discount_amount', 12, 2)->nullable()->after('tax_amount');
            $table->string('signature_path')->nullable()->after('notes');
            $table->timestamp('sent_at')->nullable()->after('signature_path');
            $table->boolean('sent_via_email')->default(false)->after('sent_at');
            $table->boolean('sent_via_whatsapp')->default(false)->after('sent_via_email');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE invoices MODIFY status ENUM('draft','unpaid','paid','overdue','cancelled') NOT NULL DEFAULT 'draft'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE invoices MODIFY status ENUM('unpaid','paid','overdue','cancelled') NOT NULL DEFAULT 'unpaid'");
        }

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'subtotal_amount',
                'uses_tax',
                'tax_rate',
                'tax_amount',
                'discount_amount',
                'signature_path',
                'sent_at',
                'sent_via_email',
                'sent_via_whatsapp',
            ]);
        });
    }
};
