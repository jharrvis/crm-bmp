<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $modules = [
            // Master Data
            'branches' => ['view', 'create', 'update', 'delete'],
            'divisions' => ['view', 'create', 'update', 'delete'],
            'employees' => ['view', 'create', 'update', 'delete'],
            'roles' => ['view', 'create', 'update', 'delete'],

            // Infrastruktur
            'routers' => ['view', 'create', 'update', 'delete', 'connect'],
            'servers' => ['view', 'create', 'update', 'delete', 'connect'],
            'services' => ['view', 'create', 'update', 'delete'],
            'packages' => ['view', 'create', 'update', 'delete'],
            'towers' => ['view', 'create', 'update', 'delete'],
            'odps' => ['view', 'create', 'update', 'delete'],

            // Bisnis
            'clients' => ['view', 'create', 'update', 'delete'],
            'subscriptions' => ['view', 'create', 'update', 'delete', 'suspend', 'activate'],
            'invoices' => ['view', 'create', 'update', 'delete', 'send', 'mark_paid'],
            'payments' => ['view', 'create', 'update', 'delete', 'verify'],

            // Support
            'tickets' => ['view', 'create', 'update', 'delete', 'assign', 'close'],
            'work_orders' => ['view', 'create', 'update', 'delete', 'assign', 'complete'],

            // Settings
            'settings' => ['view', 'update'],
            'logs' => ['view'],
        ];

        // Create all permissions
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$module}.{$action}",
                    'guard_name' => 'web',
                ]);
            }
        }

        // Update role descriptions
        $roleDescriptions = [
            'Owner' => 'Super Administrator dengan akses penuh ke semua fitur',
            'Admin' => 'Administrator dengan akses manajemen operasional',
            'Employee' => 'Karyawan dengan akses terbatas sesuai divisi',
            'Client' => 'Pelanggan dengan akses portal pelanggan',
        ];

        foreach ($roleDescriptions as $roleName => $description) {
            Role::where('name', $roleName)->update([
                'description' => $description,
                'is_system' => true,
            ]);
        }

        // Assign all permissions to Owner
        $ownerRole = Role::findByName('Owner');
        $ownerRole->givePermissionTo(Permission::all());

        // Assign most permissions to Admin (except roles and settings management)
        $adminRole = Role::findByName('Admin');
        $adminPermissions = Permission::where('name', 'not like', 'roles.%')
            ->where('name', 'not like', 'settings.%')
            ->where('name', 'not like', 'logs.%')
            ->get();
        $adminRole->givePermissionTo($adminPermissions);

        // Give Admin view-only access to roles and logs
        $adminRole->givePermissionTo(['roles.view', 'logs.view']);

        // Assign basic permissions to Employee
        $employeeRole = Role::findByName('Employee');
        $employeePermissions = [
            'branches.view',
            'divisions.view',
            'employees.view',
            'routers.view',
            'servers.view',
            'services.view',
            'packages.view',
            'clients.view',
            'clients.create',
            'clients.update',
            'subscriptions.view',
            'subscriptions.create',
            'subscriptions.update',
            'invoices.view',
            'invoices.create',
            'tickets.view',
            'tickets.create',
            'tickets.update',
            'work_orders.view',
            'work_orders.create',
            'work_orders.update',
        ];
        $employeeRole->givePermissionTo($employeePermissions);

        // Client role - minimal permissions (portal only)
        $clientRole = Role::findByName('Client');
        $clientPermissions = [
            'subscriptions.view',
            'invoices.view',
            'tickets.view',
            'tickets.create',
        ];
        $clientRole->givePermissionTo($clientPermissions);
    }
}
