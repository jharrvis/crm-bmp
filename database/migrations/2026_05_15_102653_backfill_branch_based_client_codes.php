<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $branches = DB::table('branches')
            ->select('id', 'code')
            ->orderBy('id')
            ->get();

        foreach ($branches as $branch) {
            $branchCode = strtoupper((string) $branch->code);

            $clients = DB::table('clients')
                ->select('id')
                ->where('branch_id', $branch->id)
                ->orderBy('id')
                ->get();

            foreach ($clients as $index => $client) {
                $clientCode = sprintf('%s%05d', $branchCode, $index + 1);

                DB::table('clients')
                    ->where('id', $client->id)
                    ->update([
                        'client_code' => $clientCode,
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    public function down(): void
    {
        // Backfill ini mengubah data existing dan tidak memiliki rollback otomatis yang aman.
    }
};
