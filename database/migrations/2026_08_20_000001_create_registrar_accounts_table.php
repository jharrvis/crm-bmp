<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrar_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 50)->default('srsx')->comment('Kode provider: srsx, dll');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->string('base_url')->comment('HTTPS base URL API provider');
            $table->text('api_username_encrypted')->nullable()->comment('API username terenkripsi');
            $table->text('api_password_encrypted')->nullable()->comment('API password terenkripsi');
            $table->text('settings_encrypted')->nullable()->comment('JSON terenkripsi: allowed_tlds, timeout, dll');
            $table->timestamp('last_tested_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('last_error_at')->nullable();
            $table->text('last_error_summary')->nullable()->comment('Ringkasan error aman tanpa secret');
            $table->timestamps();

            $table->index(['provider', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrar_accounts');
    }
};
