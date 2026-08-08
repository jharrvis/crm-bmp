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
        Schema::create('subscription_mail_hostings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mail_server_id')->constrained('hosting_servers')->restrictOnDelete();
            $table->string('domain');
            $table->string('admin_email')->nullable();
            $table->text('admin_password_encrypted')->nullable();
            $table->unsignedInteger('max_mailboxes')->default(0);
            $table->unsignedInteger('mailbox_quota_mb')->default(0);
            $table->unsignedInteger('alias_max')->default(0);
            $table->string('mail_server_type')->default('zimbra');
            $table->string('status')->default('active'); // active, suspended, terminated
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_mail_hostings');
    }
};
