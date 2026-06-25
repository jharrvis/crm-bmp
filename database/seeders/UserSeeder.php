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
        $roleOwner = Role::firstOrCreate(['name' => 'Owner']);
        $roleAdmin = Role::firstOrCreate(['name' => 'Admin']);
        $roleEmployee = Role::firstOrCreate(['name' => 'Employee']);
        Role::firstOrCreate(['name' => 'Billing']);
        Role::firstOrCreate(['name' => 'NOC']);
        Role::firstOrCreate(['name' => 'CS']);
        Role::firstOrCreate(['name' => 'Sales']);
        Role::firstOrCreate(['name' => 'Finance']);

        // Create Owner User
        $owner = User::updateOrCreate(
            ['email' => 'owner@bmpnet.id'],
            [
                'name' => 'Bapak Owner',
                'password' => Hash::make('password'),
            ]
        );
        $owner->assignRole($roleOwner);

        // Create Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@bmpnet.id'],
            [
                'name' => 'Admin Pusat',
                'password' => Hash::make('password'),
            ]
        );
        $admin->assignRole($roleAdmin);

        // Create Employee User
        $employee = User::updateOrCreate(
            ['email' => 'teknisi@bmpnet.id'],
            [
                'name' => 'Teknisi 1',
                'password' => Hash::make('password'),
            ]
        );
        $employee->assignRole($roleEmployee);

        // Portal client accounts use the dedicated client_portal_accounts table.
        // They are not represented as staff users or roles in the users table.
    }
}
