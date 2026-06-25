<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:roles.view')->only(['index', 'show']);
        $this->middleware('permission:roles.create')->only(['create', 'store']);
        $this->middleware('permission:roles.update')->only(['edit', 'update', 'syncPermissions']);
        $this->middleware('permission:roles.delete')->only(['destroy']);
    }

    /**
     * Display a listing of roles.
     */
    public function index()
    {
        $roles = Role::withCount('users', 'permissions')
            ->orderBy('is_system', 'desc')
            ->orderBy('name')
            ->get();

        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $roles = Role::all();
        return view('roles.create', compact('roles'));
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string|max:255',
            'copy_from' => 'nullable|exists:roles,id',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'guard_name' => 'web',
        ]);

        // Copy permissions from existing role if specified
        $sourceRole = null;
        if (!empty($validated['copy_from'])) {
            $sourceRole = Role::find($validated['copy_from']);
            $role->syncPermissions($sourceRole->permissions);
        }

        activity('roles')
            ->causedBy($request->user())
            ->performedOn($role)
            ->event('created')
            ->withProperties([
                'role_name' => $role->name,
                'copied_from' => $sourceRole->name ?? null,
                'permissions' => $role->permissions->pluck('name')->values()->all(),
            ])
            ->log('Membuat role baru');

        return redirect()->route('roles.edit', $role)
            ->with('success', 'Role berhasil dibuat. Silakan atur permissions.');
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role)
    {
        $role->loadCount('users', 'permissions');
        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        });
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('roles.show', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        $role->loadCount('users', 'permissions');

        // Group permissions by module
        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        });

        $rolePermissions = $role->permissions->pluck('name')->toArray();

        // Define module groups for better UI organization
        $moduleGroups = [
            'Master Data' => ['branches', 'divisions', 'employees'],
            'Infrastruktur' => ['routers', 'servers', 'vendors', 'metro_ethernets', 'zabbix_monitors', 'services', 'packages', 'towers', 'odps'],
            'Bisnis' => ['clients', 'subscriptions', 'invoices', 'payments', 'financial_reports'],
            'Support' => ['tickets', 'work_orders'],
            'Sistem' => ['system_updates', 'logs', 'roles', 'settings'],
        ];

        return view('roles.edit', compact('role', 'permissions', 'rolePermissions', 'moduleGroups'));
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role)
    {
        // Prevent editing system role names
        if ($role->is_system && $request->name !== $role->name) {
            return back()->with('error', 'Nama role bawaan sistem tidak dapat diubah.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:255',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $oldName = $role->name;
        $oldDescription = $role->description;
        $oldPermissions = $role->permissions->pluck('name')->sort()->values()->all();

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        // Sync permissions
        $role->syncPermissions($validated['permissions'] ?? []);

        activity('roles')
            ->causedBy($request->user())
            ->performedOn($role)
            ->event('updated')
            ->withProperties([
                'old' => [
                    'name' => $oldName,
                    'description' => $oldDescription,
                    'permissions' => $oldPermissions,
                ],
                'attributes' => [
                    'name' => $role->name,
                    'description' => $role->description,
                    'permissions' => $role->permissions->pluck('name')->sort()->values()->all(),
                ],
            ])
            ->log('Memperbarui role dan permissions');

        return redirect()->route('roles.index')
            ->with('success', 'Role dan permissions berhasil diperbarui.');
    }

    /**
     * Sync permissions via AJAX.
     */
    public function syncPermissions(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $oldPermissions = $role->permissions->pluck('name')->sort()->values()->all();
        $role->syncPermissions($validated['permissions'] ?? []);

        activity('roles')
            ->causedBy($request->user())
            ->performedOn($role)
            ->event('permissions_synced')
            ->withProperties([
                'old' => ['permissions' => $oldPermissions],
                'attributes' => ['permissions' => $role->permissions->pluck('name')->sort()->values()->all()],
            ])
            ->log('Sinkronisasi permission role');

        return response()->json([
            'success' => true,
            'message' => 'Permissions berhasil diperbarui.',
            'count' => count($validated['permissions'] ?? []),
        ]);
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role)
    {
        // Prevent deleting system roles
        if ($role->is_system) {
            return response()->json([
                'success' => false,
                'message' => 'Role bawaan sistem tidak dapat dihapus.',
            ], 403);
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Role masih digunakan oleh ' . $role->users()->count() . ' user.',
            ], 400);
        }

        activity('roles')
            ->causedBy(request()->user())
            ->performedOn($role)
            ->event('deleted')
            ->withProperties([
                'role_name' => $role->name,
                'description' => $role->description,
            ])
            ->log('Menghapus role');

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role berhasil dihapus.',
        ]);
    }
}
