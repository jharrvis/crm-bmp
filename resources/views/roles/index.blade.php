<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Manajemen Role</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola role dan hak akses pengguna</p>
            </div>
            <a href="{{ route('roles.create') }}"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl font-semibold text-sm shadow-lg shadow-blue-200 dark:shadow-blue-900/30 transition-all">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Tambah Role
            </a>
        </div>
    </x-slot>

    @push('scripts')
        @csrf
        <script>
            (function () {
                const baseUrl = '{{ url('/') }}';
                let deleteId = null;

                window.deleteRole = function (id, name) {
                    deleteId = id;
                    showConfirmModal('Hapus Role?', `Role "${name}" akan dihapus. Pastikan tidak ada user yang menggunakan role ini.`, () => {
                        const btn = document.getElementById('confirmYesBtn');
                        const spinner = document.getElementById('confirmSpinner');
                        const text = document.getElementById('confirmBtnText');
                        setButtonLoading(btn, spinner, text, true, 'Ya, Hapus!');

                        fetch(`${baseUrl}/roles/${deleteId}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ _method: 'DELETE' })
                        })
                            .then(r => r.json())
                            .then(data => {
                                setButtonLoading(btn, spinner, text, false, 'Ya, Hapus!');
                                hideConfirmModal();
                                if (data.success) {
                                    showToast(data.message, 'success');
                                    setTimeout(() => location.reload(), 1000);
                                } else {
                                    showToast(data.message, 'error');
                                }
                            })
                            .catch(() => {
                                setButtonLoading(btn, spinner, text, false, 'Ya, Hapus!');
                                showToast('Terjadi kesalahan!', 'error');
                            });
                    });
                };
            })();
        </script>
    @endpush

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($roles as $role)
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden group">
                {{-- Card Header --}}
                <div class="p-6 pb-4">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center
                                    @if($role->name === 'Owner') bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400
                                    @elseif($role->name === 'Admin') bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400
                                    @elseif($role->name === 'Employee') bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400
                                    @elseif($role->name === 'Client') bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400
                                    @else bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400
                                    @endif">
                                @if($role->name === 'Owner')
                                    <i data-lucide="crown" class="w-6 h-6"></i>
                                @elseif($role->name === 'Admin')
                                    <i data-lucide="shield-check" class="w-6 h-6"></i>
                                @elseif($role->name === 'Employee')
                                    <i data-lucide="user-cog" class="w-6 h-6"></i>
                                @elseif($role->name === 'Client')
                                    <i data-lucide="user" class="w-6 h-6"></i>
                                @else
                                    <i data-lucide="key" class="w-6 h-6"></i>
                                @endif
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-slate-800 dark:text-white">{{ $role->name }}</h3>
                                @if($role->is_system)
                                    <span
                                        class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                        <i data-lucide="lock" class="w-3 h-3"></i> Sistem
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4 line-clamp-2">
                        {{ $role->description ?? 'Tidak ada deskripsi' }}
                    </p>

                    {{-- Stats --}}
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2 text-sm">
                            <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
                                <i data-lucide="users" class="w-4 h-4 text-slate-500"></i>
                            </div>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $role->users_count }}</span>
                            <span class="text-slate-400 text-xs">Users</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
                                <i data-lucide="key" class="w-4 h-4 text-slate-500"></i>
                            </div>
                            <span
                                class="font-semibold text-slate-700 dark:text-slate-300">{{ $role->permissions_count }}</span>
                            <span class="text-slate-400 text-xs">Permissions</span>
                        </div>
                    </div>

                    @if($role->users->isNotEmpty())
                        <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Pengguna dengan Role Ini</p>
                            <div class="space-y-2">
                                @foreach($role->users->take(3) as $user)
                                    <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 dark:bg-slate-700/40 px-3 py-2">
                                        <div class="min-w-0">
                                            <div class="truncate text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $user->name }}</div>
                                            <div class="truncate text-xs text-slate-400">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if($role->users_count > 3)
                                <div class="mt-2 text-xs font-medium text-slate-400">
                                    +{{ $role->users_count - 3 }} user lainnya
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Card Footer / Actions --}}
                <div
                    class="px-6 py-4 bg-slate-50 dark:bg-slate-700/30 border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-2">
                    <a href="{{ route('roles.show', $role) }}"
                        class="p-2 text-slate-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
                        title="Lihat Detail">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </a>
                    <a href="{{ route('roles.edit', $role) }}"
                        class="p-2 text-slate-500 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/30 rounded-lg transition-colors"
                        title="Edit Permissions">
                        <i data-lucide="settings" class="w-4 h-4"></i>
                    </a>
                    @if(!$role->is_system)
                        <button onclick="deleteRole({{ $role->id }}, '{{ $role->name }}')"
                            class="p-2 text-slate-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors"
                            title="Hapus Role">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    @else
                        <span class="p-2 text-slate-300 dark:text-slate-600 cursor-not-allowed"
                            title="Role sistem tidak dapat dihapus">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    @if($roles->isEmpty())
        <div class="text-center py-12">
            <div
                class="w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-lucide="shield-off" class="w-8 h-8 text-slate-400"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-2">Belum Ada Role</h3>
            <p class="text-slate-500 dark:text-slate-400">Mulai dengan membuat role baru untuk mengatur hak akses.</p>
        </div>
    @endif

    {{-- Confirm Modal Component --}}
    @include('components.confirm-modal')
</x-app-layout>
