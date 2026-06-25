<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('roles.index') }}"
                class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5 text-slate-500"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Detail Role: {{ $role->name }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Lihat permissions yang dimiliki role ini</p>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column: Role Info --}}
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <div class="p-6 text-center border-b border-slate-100 dark:border-slate-700">
                    <div class="w-20 h-20 rounded-2xl mx-auto mb-4 flex items-center justify-center
                        @if($role->name === 'Owner') bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400
                        @elseif($role->name === 'Admin') bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400
                        @elseif($role->name === 'Employee') bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400
                        @elseif($role->name === 'Client') bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400
                        @else bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400
                        @endif">
                        @if($role->name === 'Owner')
                            <i data-lucide="crown" class="w-10 h-10"></i>
                        @elseif($role->name === 'Admin')
                            <i data-lucide="shield-check" class="w-10 h-10"></i>
                        @elseif($role->name === 'Employee')
                            <i data-lucide="user-cog" class="w-10 h-10"></i>
                        @elseif($role->name === 'Client')
                            <i data-lucide="user" class="w-10 h-10"></i>
                        @else
                            <i data-lucide="key" class="w-10 h-10"></i>
                        @endif
                    </div>
                    <h2 class="text-xl font-bold text-slate-800 dark:text-white">{{ $role->name }}</h2>
                    @if($role->is_system)
                        <span
                            class="inline-flex items-center gap-1 mt-2 text-xs font-bold text-slate-400 bg-slate-100 dark:bg-slate-700 px-3 py-1 rounded-full">
                            <i data-lucide="lock" class="w-3 h-3"></i> Role Sistem
                        </span>
                    @endif
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Deskripsi</p>
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            {{ $role->description ?? 'Tidak ada deskripsi' }}
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-100 dark:border-slate-700">
                        <div class="text-center p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                            <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ $role->users_count }}</p>
                            <p class="text-xs text-slate-500 mt-1">Users</p>
                        </div>
                        <div class="text-center p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                            <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ $role->permissions_count }}
                            </p>
                            <p class="text-xs text-slate-500 mt-1">Permissions</p>
                        </div>
                    </div>
                    <div class="pt-4">
                        <a href="{{ route('roles.edit', $role) }}"
                            class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-xl font-semibold text-sm shadow-lg shadow-blue-200 dark:shadow-blue-900/30 transition-all">
                            <i data-lucide="settings" class="w-4 h-4"></i>
                            Edit Permissions
                        </a>
                    </div>
                </div>
            </div>

            <div class="mt-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <div class="p-6 border-b border-slate-100 dark:border-slate-700">
                    <h2 class="font-bold text-lg text-slate-800 dark:text-white">Pengguna dengan Role Ini</h2>
                </div>
                <div class="p-6">
                    @if($role->users->isNotEmpty())
                        <div class="space-y-3">
                            @foreach($role->users as $user)
                                <div class="rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3">
                                    <div class="text-sm font-semibold text-slate-800 dark:text-white">{{ $user->name }}</div>
                                    <div class="mt-1 text-xs text-slate-400">{{ $user->email }}</div>
                                    @if($user->branch || $user->division)
                                        <div class="mt-2 flex flex-wrap gap-2 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                                            @if($user->branch)
                                                <span class="rounded-full bg-slate-100 dark:bg-slate-700 px-2.5 py-1">Cabang: {{ $user->branch->name }}</span>
                                            @endif
                                            @if($user->division)
                                                <span class="rounded-full bg-slate-100 dark:bg-slate-700 px-2.5 py-1">Divisi: {{ $user->division->name }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-500 dark:text-slate-400">Belum ada user yang menggunakan role ini.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right Column: Permissions List --}}
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <div class="p-6 border-b border-slate-100 dark:border-slate-700">
                    <h2 class="font-bold text-lg text-slate-800 dark:text-white">Daftar Permissions</h2>
                </div>
                <div class="p-6">
                    @if(count($rolePermissions) > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($permissions as $module => $perms)
                                @php
                                    $modulePermissions = $perms->filter(function ($p) use ($rolePermissions) {
                                        return in_array($p->name, $rolePermissions);
                                    });
                                @endphp
                                @if($modulePermissions->count() > 0)
                                    <div class="border border-slate-200 dark:border-slate-700 rounded-xl p-4">
                                        <h4 class="font-bold text-sm text-slate-700 dark:text-slate-300 capitalize mb-3">
                                            {{ str_replace('_', ' ', $module) }}
                                        </h4>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($modulePermissions as $perm)
                                                @php
                                                    $action = explode('.', $perm->name)[1];
                                                @endphp
                                                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-1 rounded-md
                                                                                    @if($action === 'view') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400
                                                                                    @elseif($action === 'create') bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400
                                                                                    @elseif($action === 'update') bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400
                                                                                    @elseif($action === 'delete') bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400
                                                                                    @else bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400
                                                                                    @endif">
                                                    {{ ucfirst(str_replace('_', ' ', $action)) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div
                                class="w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i data-lucide="shield-off" class="w-8 h-8 text-slate-400"></i>
                            </div>
                            <p class="text-slate-500">Role ini belum memiliki permission apapun.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
