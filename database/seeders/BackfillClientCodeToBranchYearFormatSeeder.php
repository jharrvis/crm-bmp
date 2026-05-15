<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BackfillClientCodeToBranchYearFormatSeeder extends Seeder
{
    /**
     * Update existing client codes to the new format:
     * {branch_id}{year}{sequence}
     *
     * Example: 126001
     * - 1   => branch id
     * - 26  => registration year (forced to 26 for existing data)
     * - 001 => sequence per branch
     */
    public function run(): void
    {
        $forcedYear = '26';

        $branches = DB::table('branches')
            ->select('id')
            ->orderBy('id')
            ->get();

        DB::transaction(function () use ($branches, $forcedYear) {
            foreach ($branches as $branch) {
                $clients = DB::table('clients')
                    ->select('id', 'registered_at')
                    ->where('branch_id', $branch->id)
                    ->orderBy('id')
                    ->get();

                foreach ($clients as $index => $client) {
                    $clientCode = sprintf('%d%s%03d', $branch->id, $forcedYear, $index + 1);

                    DB::table('clients')
                        ->where('id', $client->id)
                        ->update([
                            'client_code' => $clientCode,
                            'registered_at' => $client->registered_at ?? now()->toDateString(),
                            'updated_at' => now(),
                        ]);
                }
            }
        });
    }
}
