<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use App\Models\Division;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    private const STAFF_ROLE_NAMES = ['Owner', 'Admin', 'Employee', 'Billing', 'NOC', 'CS', 'Sales', 'Finance'];

    /**
     * Display a listing of the resource.
     */
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employees = User::role(self::STAFF_ROLE_NAMES)
            ->with(['branch', 'division', 'roles'])
            ->latest()
            ->get();

        $branches = Branch::all();
        $divisions = Division::all();
        $roles = Role::whereIn('name', self::STAFF_ROLE_NAMES)->get();

        return view('employees.index', compact('employees', 'branches', 'divisions', 'roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $branches = Branch::all();
        $divisions = Division::all();
        $roles = Role::whereIn('name', self::STAFF_ROLE_NAMES)->get();
        return view('employees.create', compact('branches', 'divisions', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'max:2048'], // Max 2MB
            'role' => ['required', 'string', 'exists:roles,name'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'division_id' => ['nullable', 'exists:divisions,id'],
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'avatar' => $avatarPath,
            'branch_id' => $request->branch_id,
            'division_id' => $request->division_id,
        ]);

        $user->assignRole($request->role);

        if ($request->wantsJson()) {
            $user->load(['branch', 'division', 'roles']);
            return response()->json([
                'success' => true,
                'message' => 'Karyawan berhasil ditambahkan.',
                'employee' => $user
            ]);
        }

        return redirect()->route('employees.index')
            ->with('success', 'Karyawan berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, User $employee)
    {
        if ($request->wantsJson() || $request->ajax()) {
            $employee->load(['branch', 'division', 'roles']);
            return response()->json($employee);
        }
        return view('employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $employee)
    {
        $branches = Branch::all();
        $divisions = Division::all();
        $roles = Role::whereIn('name', self::STAFF_ROLE_NAMES)->get();
        return view('employees.edit', compact('employee', 'branches', 'divisions', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $employee)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $employee->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'division_id' => ['nullable', 'exists:divisions,id'],
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'branch_id' => $request->branch_id,
            'division_id' => $request->division_id,
        ];

        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($employee->avatar) {
                Storage::disk('public')->delete($employee->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Rules\Password::defaults()],
            ]);
            $data['password'] = Hash::make($request->password);
        }

        $employee->update($data);
        $employee->syncRoles([$request->role]);

        if ($request->wantsJson()) {
            $employee->load(['branch', 'division', 'roles']);
            return response()->json([
                'success' => true,
                'message' => 'Data karyawan berhasil diperbarui.',
                'employee' => $employee
            ]);
        }

        return redirect()->route('employees.index')
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $employee)
    {
        if ($employee->id === auth()->id()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak dapat menghapus akun sendiri.'
                ], 403);
            }
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $employee->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Karyawan berhasil dihapus.'
            ]);
        }

        return redirect()->route('employees.index')
            ->with('success', 'Karyawan berhasil dihapus.');
    }
}
