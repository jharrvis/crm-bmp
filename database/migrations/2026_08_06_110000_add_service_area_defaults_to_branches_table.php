<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('default_province_code', 20)->nullable()->after('phone');
            $table->string('default_regency_code', 20)->nullable()->after('default_province_code');
            $table->decimal('default_latitude', 10, 8)->nullable()->after('default_regency_code');
            $table->decimal('default_longitude', 11, 8)->nullable()->after('default_latitude');
        });

        foreach ([
            'SLT' => ['33', '33.73', -7.33194400, 110.49277800],
            'SMG' => ['33', '33.74', -6.96666700, 110.41666700],
            'KDS' => ['33', '33.19', -6.80500000, 110.84000000],
        ] as $code => [$province, $regency, $latitude, $longitude]) {
            DB::table('branches')->where('code', $code)->update([
                'default_province_code' => $province,
                'default_regency_code' => $regency,
                'default_latitude' => $latitude,
                'default_longitude' => $longitude,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['default_province_code', 'default_regency_code', 'default_latitude', 'default_longitude']);
        });
    }
};
