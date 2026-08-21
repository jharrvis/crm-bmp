<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrar_operations', function (Blueprint $table) {
            try {
                $table->dropForeign(['registrar_account_id']);
            } catch (\Throwable $e) {
            }
            $table->foreign('registrar_account_id')
                ->references('id')->on('registrar_accounts')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('registrar_operations', function (Blueprint $table) {
            try {
                $table->dropForeign(['registrar_account_id']);
            } catch (\Throwable $e) {
            }
            $table->foreign('registrar_account_id')
                ->references('id')->on('registrar_accounts')
                ->cascadeOnDelete();
        });
    }
};
