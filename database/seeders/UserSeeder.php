<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Roles
        $roleOwner = Role::create(['name' => 'Owner']);
        $roleAdmin = Role::create(['name' => 'Admin']);
        $roleEmployee = Role::create(['name' => 'Employee']);
        $roleClient = Role::create(['name' => 'Client']);

        // Create Owner User
        $owner = User::create([
            'name' => 'Bapak Owner',
            'email' => 'owner@bmpnet.id',
            'password' => Hash::make('password'),
        ]);
        $owner->assignRole($roleOwner);

        // Create Admin User
        $admin = User::create([
            'name' => 'Admin Pusat',
            'email' => 'admin@bmpnet.id',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole($roleAdmin);

        // Create Employee User
        $employee = User::create([
            'name' => 'Teknisi 1',
            'email' => 'teknisi@bmpnet.id',
            'password' => Hash::make('password'),
        ]);
        $employee->assignRole($roleEmployee);

        // Create Client User
        $client = User::create([
            'name' => 'Pelanggan A',
            'email' => 'client@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $client->assignRole($roleClient);
    }
}

