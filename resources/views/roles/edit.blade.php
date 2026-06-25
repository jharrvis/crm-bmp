<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('roles.index') }}"
                class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5 text-slate-500"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Edit Role: {{ $role->name }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    {{ $role->users_count }} user · {{ $role->permissions_count }} permissions
                </p>
            </div>
        </div>
    </x-slot>

    <form action="{{ route('roles.update', $role) }}" method="POST" id="roleForm">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column: Role Info --}}
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm sticky top-24">
                    <div class="p-6 border-b border-slate-100 dark:border-slate-700">
                        <h2 class="font-bold text-lg text-slate-800 dark:text-white">Informasi Role</h2>
                    </div>
                    <div class="p-6 space-y-5">
                        {{-- Role Name --}}
                        <div>
                            <label for="name" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                                Nama Role <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="name" name="name" value="{{ old('name', $role->name) }}" required
                                {{ $role->is_system ? 'readonly' : '' }}
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all {{ $role->is_system ? 'opacity-60 cursor-not-allowed' : '' }}">
                            @if($role->is_system)
                                <p class="mt-2 text-xs text-slate-400">Nama role sistem tidak dapat diubah.</p>
                            @endif
                            @error('name')
                                <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div>
                            <label for="description" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                                Deskripsi
                            </label>
                            <textarea id="description" name="description" rows="3"
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all resize-none"
                                placeholder="Deskripsi singkat...">{{ old('description', $role->description) }}</textarea>
                        </div>

                        {{-- Stats --}}
                        <div class="pt-4 border-t border-slate-100 dark:border-slate-700">
                            <div class="flex items-center justify-between text-sm mb-2">
                                <span class="text-slate-500">Total Permissions</span>
                                <span id="permissionCount" class="font-bold text-slate-800 dark:text-white">{{ count($rolePermissions) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-500">Users dengan Role ini</span>
                                <span class="font-bold text-slate-800 dark:text-white">{{ $role->users_count }}</span>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100 dark:border-slate-700">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">User dengan Role Ini</p>
                            @if($role->users->isNotEmpty())
                                <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                                    @foreach($role->users as $user)
                                        <div class="rounded-xl bg-slate-50 dark:bg-slate-700/50 px-3 py-2">
                                            <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $user->name }}</div>
                                            <div class="text-xs text-slate-400">{{ $user->email }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-slate-500 dark:text-slate-400">Belum ada user.</p>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="pt-4">
                            <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-xl font-semibold text-sm shadow-lg shadow-blue-200 dark:shadow-blue-900/30 transition-all">
                                <i data-lucide="save" class="w-4 h-4"></i>
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Permissions Matrix --}}
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                    <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                        <div>
                            <h2 class="font-bold text-lg text-slate-800 dark:text-white">Permissions</h2>
                            <p class="text-sm text-slate-500 mt-1">Centang permissions yang diizinkan untuk role ini</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="button" onclick="selectAll()"
                                class="text-xs font-semibold text-blue-600 hover:text-blue-700 px-3 py-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors">
                                Pilih Semua
                            </button>
                            <button type="button" onclick="deselectAll()"
                                class="text-xs font-semibold text-slate-500 hover:text-slate-700 px-3 py-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                Hapus Semua
                            </button>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        @foreach($moduleGroups as $groupName => $modules)
                            <div class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                                {{-- Group Header --}}
                                <div class="bg-slate-50 dark:bg-slate-700/50 px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                                    <div class="flex items-center justify-between">
                                        <h3 class="font-bold text-sm text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                            {{ $groupName }}
                                        </h3>
                                        <button type="button" onclick="selectGroup('{{ Str::slug($groupName) }}')"
                                            class="text-[10px] font-bold text-blue-600 hover:text-blue-700 uppercase tracking-wider">
                                            Pilih Semua
                                        </button>
                                    </div>
                                </div>

                                {{-- Permissions Table --}}
                                <div class="overflow-x-auto">
                                    <table class="w-full">
                                        <thead>
                                            <tr class="text-xs text-slate-500 uppercase tracking-wider">
                                                <th class="text-left px-4 py-3 font-bold">Modul</th>
                                                @php
                                                    $allActions = ['view', 'create', 'update', 'delete', 'connect', 'assign', 'close', 'complete', 'suspend', 'activate', 'send', 'mark_paid', 'verify'];
                                                    $groupActions = [];
                                                    foreach ($modules as $module) {
                                                        if (isset($permissions[$module])) {
                                                            foreach ($permissions[$module] as $perm) {
                                                                $action = explode('.', $perm->name)[1];
                                                                if (!in_array($action, $groupActions)) {
                                                                    $groupActions[] = $action;
                                                                }
                                                            }
                                                        }
                                                    }
                                                    // Sort by common order
                                                    $actionOrder = array_flip($allActions);
                                                    usort($groupActions, fn($a, $b) => ($actionOrder[$a] ?? 99) <=> ($actionOrder[$b] ?? 99));
                                                @endphp
                                                @foreach($groupActions as $action)
                                                    <th class="text-center px-3 py-3 font-bold">
                                                        {{ ucfirst(str_replace('_', ' ', $action)) }}
                                                    </th>
                                                @endforeach
                                                <th class="px-3 py-3"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                            @foreach($modules as $module)
                                                @if(isset($permissions[$module]))
                                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors" data-group="{{ Str::slug($groupName) }}">
                                                        <td class="px-4 py-3">
                                                            <span class="font-semibold text-sm text-slate-700 dark:text-slate-300 capitalize">
                                                                {{ str_replace('_', ' ', $module) }}
                                                            </span>
                                                        </td>
                                                        @foreach($groupActions as $action)
                                                            <td class="text-center px-3 py-3">
                                                                @php
                                                                    $permName = "{$module}.{$action}";
                                                                    $hasPermission = $permissions[$module]->contains('name', $permName);
                                                                @endphp
                                                                @if($hasPermission)
                                                                    <label class="inline-flex items-center justify-center cursor-pointer">
                                                                        <input type="checkbox" name="permissions[]" value="{{ $permName }}"
                                                                            class="permission-checkbox w-5 h-5 rounded-md border-2 border-slate-300 dark:border-slate-500 text-blue-600 focus:ring-blue-500 focus:ring-offset-0 transition-colors cursor-pointer"
                                                                            {{ in_array($permName, $rolePermissions) ? 'checked' : '' }}
                                                                            onchange="updateCount()">
                                                                    </label>
                                                                @else
                                                                    <span class="text-slate-300 dark:text-slate-600">—</span>
                                                                @endif
                                                            </td>
                                                        @endforeach
                                                        <td class="px-3 py-3">
                                                            <button type="button" onclick="selectRow(this)"
                                                                class="text-[10px] font-bold text-blue-600 hover:text-blue-700 uppercase tracking-wider whitespace-nowrap">
                                                                All
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
        <script>
            function updateCount() {
                const count = document.querySelectorAll('.permission-checkbox:checked').length;
                document.getElementById('permissionCount').textContent = count;
            }

            function selectAll() {
                document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = true);
                updateCount();
            }

            function deselectAll() {
                document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
                updateCount();
            }

            function selectGroup(groupSlug) {
                document.querySelectorAll(`tr[data-group="${groupSlug}"] .permission-checkbox`).forEach(cb => cb.checked = true);
                updateCount();
            }

            function selectRow(btn) {
                const row = btn.closest('tr');
                const checkboxes = row.querySelectorAll('.permission-checkbox');
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                checkboxes.forEach(cb => cb.checked = !allChecked);
                updateCount();
            }
        </script>
    @endpush
</x-app-layout>
