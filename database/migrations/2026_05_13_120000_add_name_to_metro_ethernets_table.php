<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('metro_ethernets', function (Blueprint $table) {
            $table->string('name')->nullable()->after('vendor_id');
        });

        DB::table('metro_ethernets')
            ->select('id', 'cid')
            ->orderBy('id')
            ->get()
            ->each(function (object $metro): void {
                DB::table('metro_ethernets')
                    ->where('id', $metro->id)
                    ->update([
                        'name' => $metro->cid
                            ? 'Metro Ethernet ' . $metro->cid
                            : 'Metro Ethernet #' . $metro->id,
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('metro_ethernets', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
