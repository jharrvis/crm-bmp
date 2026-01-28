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
        Schema::create('hosting_servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('host'); // IP or Domain
            $table->string('port')->default('8083');
            $table->string('username')->nullable(); // Admin user
            $table->text('api_key')->nullable(); // Encrypted API Key or Access Key
            $table->text('secret_key')->nullable(); // Encrypted Secret Key (if needed)
            $table->string('type')->default('hestiacp'); // hestiacp, cpanel, etc
            $table->string('location')->nullable();
            $table->integer('max_accounts')->default(0); // 0 = unlimited
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hosting_servers');
    }
};
