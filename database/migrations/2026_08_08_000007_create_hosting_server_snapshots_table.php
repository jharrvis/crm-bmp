<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hosting_server_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hosting_server_id')->constrained('hosting_servers')->cascadeOnDelete();
            $table->text('summary_json')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('last_synced_at')->nullable();
            $table->text('error_message')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['hosting_server_id', 'is_active'], 'hss_server_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hosting_server_snapshots');
    }
};