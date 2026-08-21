<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrar_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registrar_account_id')->constrained('registrar_accounts')->cascadeOnDelete();
            $table->foreignId('subscription_domain_id')->nullable()->constrained('subscription_domains')->nullOnDelete();
            $table->string('operation_type', 50)->comment('test_connection, sync, link, register, renew, transfer, update_nameservers, etc');
            $table->string('status', 30)->default('pending')->comment('pending, processing, completed, failed, manual_review');
            $table->json('request_payload_redacted')->nullable()->comment('Request JSON ter-redaksi tanpa secret');
            $table->json('response_payload_redacted')->nullable()->comment('Response JSON ter-redaksi');
            $table->string('idempotency_key', 100)->nullable()->unique();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error_summary')->nullable()->comment('Ringkasan error aman');
            $table->timestamps();

            $table->index(['registrar_account_id', 'status']);
            $table->index(['subscription_domain_id', 'operation_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrar_operations');
    }
};
