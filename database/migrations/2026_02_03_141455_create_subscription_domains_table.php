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
        Schema::create('subscription_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->string('domain_name');
            $table->string('registrar')->nullable()->comment('Nama registrar domain');
            $table->text('auth_code_encrypted')->nullable()->comment('EPP/Auth code (encrypted)');
            $table->date('registered_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->json('dns_records')->nullable()->comment('Catatan DNS dalam format JSON');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_domains');
    }
};
