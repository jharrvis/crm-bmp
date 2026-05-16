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
            'financial_reports' => ['view'],

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
            'Employee' => 'Role legacy karyawan umum dengan akses operasional dasar',
            'Billing' => 'Tim billing dan keuangan operasional',
            'NOC' => 'Tim Network Operation Center untuk koneksi dan gangguan teknis',
            'CS' => 'Customer service untuk pelanggan dan koordinasi layanan',
            'Sales' => 'Tim sales untuk akuisisi pelanggan dan penawaran layanan',
            'Finance' => 'Tim finance untuk laporan dan kontrol keuangan',
            'Client' => 'Pelanggan dengan akses portal pelanggan',
        ];

        foreach ($roleDescriptions as $roleName => $description) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ], [
                'description' => $description,
                'is_system' => true,
            ])->update([
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

        $billingRole = Role::findByName('Billing');
        $billingRole->syncPermissions([
            'clients.view',
            'subscriptions.view',
            'invoices.view',
            'invoices.create',
            'invoices.update',
            'invoices.delete',
            'invoices.send',
            'invoices.mark_paid',
            'payments.view',
            'payments.create',
            'payments.update',
            'payments.verify',
            'financial_reports.view',
            'tickets.view',
            'tickets.create',
            'tickets.update',
        ]);

        $nocRole = Role::findByName('NOC');
        $nocRole->syncPermissions([
            'clients.view',
            'subscriptions.view',
            'subscriptions.update',
            'subscriptions.suspend',
            'subscriptions.activate',
            'routers.view',
            'routers.connect',
            'servers.view',
            'tickets.view',
            'tickets.update',
            'tickets.assign',
            'tickets.close',
            'work_orders.view',
            'work_orders.create',
            'work_orders.update',
            'work_orders.assign',
            'work_orders.complete',
        ]);

        $csRole = Role::findByName('CS');
        $csRole->syncPermissions([
            'clients.view',
            'clients.create',
            'clients.update',
            'subscriptions.view',
            'subscriptions.create',
            'subscriptions.update',
            'invoices.view',
            'tickets.view',
            'tickets.create',
            'tickets.update',
        ]);

        $salesRole = Role::findByName('Sales');
        $salesRole->syncPermissions([
            'clients.view',
            'clients.create',
            'clients.update',
            'subscriptions.view',
            'subscriptions.create',
            'services.view',
            'packages.view',
            'tickets.view',
            'tickets.create',
        ]);

        $financeRole = Role::findByName('Finance');
        $financeRole->syncPermissions([
            'clients.view',
            'subscriptions.view',
            'invoices.view',
            'invoices.create',
            'invoices.update',
            'invoices.delete',
            'invoices.send',
            'invoices.mark_paid',
            'payments.view',
            'payments.create',
            'payments.update',
            'payments.verify',
            'financial_reports.view',
        ]);

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
