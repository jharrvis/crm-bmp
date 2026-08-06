<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Legacy address fields stay intact; these fields are optional enrichment.
            $table->string('rt', 5)->nullable()->after('address');
            $table->string('rw', 5)->nullable()->after('rt');
            $table->string('province_code', 20)->nullable()->after('city');
            $table->string('regency_code', 20)->nullable()->after('province_code');
            $table->string('district_code', 20)->nullable()->after('regency_code');
            $table->string('village_code', 20)->nullable()->after('district_code');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['rt', 'rw', 'province_code', 'regency_code', 'district_code', 'village_code']);
        });
    }
};
