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
        Schema::create('subscription_hostings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hosting_server_id')->nullable()->constrained('hosting_servers')->nullOnDelete();

            $table->string('domain')->nullable();
            $table->string('username')->nullable();
            $table->text('password_encrypted')->nullable();

            $table->integer('disk_quota_gb')->default(0);
            $table->integer('email_accounts')->default(0);
            $table->integer('databases')->default(0);

            $table->date('ssl_expiry')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_hostings');
    }
};
