<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\Branch;
use App\Models\ClientContact;
use Illuminate\Support\Str;

class SemarangClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branch = Branch::where('name', 'like', '%Semarang%')->first();

        if (!$branch) {
            $this->command->error('Branch Semarang tidak ditemukan!');
            return;
        }

        $clientNames = [
            'Hotel Grand Mega Cepu',
            'PT. TPINC Trading Jakarta',
            'PT. Formosa Bag Indonesia Tegowanu',
            'PT. Formosa Bag Indonesia KIC',
            'PT. Wifiku Indonesia / Ych',
            'Rumah Viar',
            'PT. Dian Cipta Perkasa',
            'Rudy Butiq',
            'Rumah Pak Niko',
            'PT. Triangle Motorindo / Viar',
            'PT Euro Design',
            // 'PT Euro Design', // Duplicate removed
            'PT Ada Perkasa Sahitaguna',
            'Ada Siliwangi',
            'Pandu Logistic',
            'PT. Mega Indah Cargo',
            'Gor Waikiki',
            'Bu Mega Gudang KIC',
            'Gudang PT. Keivit Indonesia',
            'Ada Fatmawati',
            'Ada Majapahit',
            'PT. SCM Enteprises',
            'Mess Samsam',
            'PT. Growell Indo Metal',
            'Pak Hanping',
        ];

        foreach ($clientNames as $index => $name) {
            $client = Client::where('name', $name)
                ->where('branch_id', $branch->id)
                ->first();

            if (!$client) {
                // Generate code for NEW client
                $count = Client::where('branch_id', $branch->id)->count();
                $nextNumber = $count + 1;
                $code = sprintf("C-%s-%04d", strtoupper(Str::slug($branch->name)), $nextNumber);

                // Ensure uniqueness
                while (Client::where('client_code', $code)->exists()) {
                    $nextNumber++;
                    $code = sprintf("C-%s-%04d", strtoupper(Str::slug($branch->name)), $nextNumber);
                }

                $client = Client::create([
                    'name' => $name,
                    'branch_id' => $branch->id,
                    'client_code' => $code,
                    'type' => 'business',
                    'status' => 'active',
                    'address' => 'Semarang, Jawa Tengah',
                    'city' => 'Semarang',
                ]);
            } else {
                $client->update([
                    'type' => 'business',
                    'status' => 'active',
                    'address' => 'Semarang, Jawa Tengah',
                    'city' => 'Semarang',
                ]);
            }

            // Add/Update PIC contact
            ClientContact::updateOrCreate(
                ['client_id' => $client->id, 'name' => 'PIC ' . $name],
                [
                    'phone' => '0812' . rand(10000000, 99999999),
                    'is_primary' => true
                ]
            );
        }

        $this->command->info('Seeding ' . count($clientNames) . ' clients for Semarang sukses!');
    }
}
