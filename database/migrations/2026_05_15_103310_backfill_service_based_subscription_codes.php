<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $clients = DB::table('clients')
            ->select('id', 'client_code')
            ->orderBy('id')
            ->get();

        foreach ($clients as $client) {
            $subscriptions = DB::table('subscriptions')
                ->join('packages', 'packages.id', '=', 'subscriptions.package_id')
                ->join('services', 'services.id', '=', 'packages.service_id')
                ->where('subscriptions.client_id', $client->id)
                ->orderBy('services.id')
                ->orderBy('subscriptions.id')
                ->get([
                    'subscriptions.id',
                    'services.code as service_code',
                    'services.id as service_id',
                ]);

            $sequenceByService = [];

            foreach ($subscriptions as $subscription) {
                $serviceCode = strtoupper((string) ($subscription->service_code ?: 'SRV'));
                $serviceKey = (string) $subscription->service_id;
                $sequenceByService[$serviceKey] = ($sequenceByService[$serviceKey] ?? 0) + 1;

                $subscriptionCode = sprintf(
                    '%s-%s%02d',
                    $client->client_code,
                    $serviceCode,
                    $sequenceByService[$serviceKey]
                );

                DB::table('subscriptions')
                    ->where('id', $subscription->id)
                    ->update([
                        'subscription_code' => $subscriptionCode,
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    public function down(): void
    {
        // Data backfill ini tidak memiliki rollback otomatis yang aman.
    }
};
