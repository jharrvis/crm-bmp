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
        Schema::create('mailboxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_mail_hosting_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('zimbra_id')->nullable();
            $table->string('display_name')->nullable();
            $table->text('password_encrypted')->nullable();
            $table->unsignedInteger('quota_mb')->default(0);
            $table->unsignedInteger('alias_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['subscription_mail_hosting_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mailboxes');
    }
};