<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('administrative_areas', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('parent_code', 20)->nullable()->index();
            $table->string('level', 20)->index();
            $table->string('name');
            $table->timestamps();

            $table->index(['level', 'parent_code', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('administrative_areas');
    }
};
