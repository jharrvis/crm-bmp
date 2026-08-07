<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ip_transits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('cid', 100)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('ip_gateway', 45)->nullable();
            $table->string('as_number', 20)->nullable();
            $table->unsignedInteger('bandwidth');
            $table->timestamps();

            $table->index('vendor_id');
            $table->index('cid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_transits');
    }
};
