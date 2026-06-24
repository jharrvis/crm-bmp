<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class WebmasterUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ownerRole = Role::firstOrCreate([
            'name' => 'Owner',
            'guard_name' => 'web',
        ]);

        $user = User::updateOrCreate(
            ['email' => 'webmaster@bmp.net.id'],
            [
                'name' => 'Webmaster BMPnet',
                'password' => Hash::make('password'),
            ]
        );

        // Give full access through the Owner role.
        $user->syncRoles([$ownerRole]);
    }
}
