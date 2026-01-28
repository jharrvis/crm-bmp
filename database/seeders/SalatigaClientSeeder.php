<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Client;
use App\Models\Branch;
use App\Models\ClientContact;
use Illuminate\Support\Str;

class SalatigaClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branch = Branch::where('name', 'like', '%Salatiga%')->first();

        if (!$branch) {
            $this->command->error('Branch Salatiga tidak ditemukan!');
            return;
        }

        $clientNames = [
            'Dong Bang Indo',
            'Inocycle Technology Group Tbk',
            'Kievit Indonesia',
            'Muara Krakatau',
            'Sam Kyung Jaya Garments',
            'Sam Kyung Jaya Busana',
            'Sam Sam Jaya Garments',
            'Semarang Garment',
            'Star Fashion Ungaran',
            'TMNC Manufacturing International',
            'TMNK Manufacturing Worldwide',
            'TMNN Manufacturing Global',
            'Des Teknologi Informasi',
            'Triangle Motorindo',
            'Dian Cipta Perkasa',
            'Wifiku Indonesia', // Cleaned from ( smrg )
            'EURO-DESIGN',
            'SCM Enteprises Apparel',
            'Ada Perkasa Sahitaguna',
            'GROWELL INDO METAL',
            'TPINC Trading Jakarta',
            'Formosa Bag Indonesia',
            'Lintas Data Prima',
            'Merdeka Panji Mulia',
            'Starcam Apparel Indonesia',
            'DCP Travelling Products',
            'JINLIN LUGGAGE INDONESIA',
            'Donglong Textile Indonesia',
        ];

        foreach ($clientNames as $index => $name) {
            $client = Client::updateOrCreate(
                ['name' => $name, 'branch_id' => $branch->id],
                [
                    'type' => 'business',
                    'status' => 'active',
                    'address' => 'Salatiga, Jawa Tengah',
                    'city' => 'Salatiga',
                ]
            );

            // Generate code ONLY if new or empty
            if (empty($client->client_code)) {
                $count = Client::where('branch_id', $branch->id)->count();
                $client->client_code = sprintf("C-%s-%04d", strtoupper(Str::slug($branch->name)), $count);
                $client->save();
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

        $this->command->info('Seeding ' . count($clientNames) . ' clients for Salatiga sukses!');
    }
}
