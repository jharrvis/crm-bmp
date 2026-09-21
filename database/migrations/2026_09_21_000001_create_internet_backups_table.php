<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internet_backups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 150);
            $table->string('circuit_id', 100)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('gateway', 45)->nullable();
            $table->unsignedInteger('bandwidth_mbps');
            $table->decimal('monthly_cost', 12, 2)->nullable();
            $table->date('active_date')->nullable();
            $table->string('address', 255)->nullable();
            $table->string('status', 20)->default('planned');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('vendor_id');
            $table->index('subscription_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internet_backups');
    }
};
