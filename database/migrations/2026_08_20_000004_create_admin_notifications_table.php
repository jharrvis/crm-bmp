<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->comment('null = broadcast ke target_role');
            $table->string('target_role', 50)->nullable()->comment('Owner, Admin, Billing, NOC, etc jika broadcast');
            $table->string('type', 50)->comment('domain_expiry_30, domain_sync_failed, hosting_ssl_expiry, etc');
            $table->string('title');
            $table->text('message');
            $table->json('payload')->nullable()->comment('subscription_id, domain_name, registrar_account_id, expires_at, days_left, error_summary ter-redaksi');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamp('expires_at')->nullable()->comment('Auto-prune setelah retention_days');
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
            $table->index(['target_role', 'read_at']);
            $table->index(['type', 'created_at']);
            $table->index(['expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notifications');
    }
};
