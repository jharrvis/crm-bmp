<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_portal_otps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_portal_account_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('code_hash');
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->unsignedInteger('attempt_count')->default(0);
            $table->string('request_ip', 45)->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['client_portal_account_id', 'created_at']);
            $table->index(['email', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_portal_otps');
    }
};
