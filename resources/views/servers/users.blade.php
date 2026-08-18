<x-app-layout>
    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6 md:p-8">

            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div>
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white">Daftar User: {{ $server->name }}</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
                        Data live dimuat dengan cache singkat. Aksi hanya tersedia untuk akun yang dikelola CRM.
                    </p>
                </div>
                <a href="{{ route('servers.manage', $server) }}"
                    class="flex items-center gap-2 border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 px-5 py-2.5 rounded-xl font-bold transition-colors">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                    Kembali
                </a>
            </div>

            <!-- Search -->
            <div class="mb-6">
                <form method="GET" action="{{ route('servers.users', $server) }}" class="flex gap-3">
                    <input type="text" name="search" value="{{ $search }}"
                        class="flex-1 rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                        placeholder="Cari username, email, atau nama...">
                    <button type="submit"
                        class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-200 dark:shadow-none transition-all">
                        <i data-lucide="search" class="w-5 h-5"></i>
                        Cari
                    </button>
                </form>
            </div>

            @if ($loadError)
                <div
                    class="flex items-center gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-900 text-red-700 dark:text-red-400 rounded-2xl px-5 py-4 text-sm">
                    <i data-lucide="alert-triangle" class="w-5 h-5 shrink-0"></i>
                    {{ $loadError }}
                </div>
            @else
                <div class="overflow-x-auto no-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-700">
                                <th class="p-4 pl-6">Username</th>
                                <th class="p-4">Kontak</th>
                                <th class="p-4">Paket</th>
                                <th class="p-4">Status</th>
                                <th class="p-4">Keterkaitan CRM</th>
                                <th class="p-4 pr-6 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @forelse ($users as $user)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="p-4 pl-6">
                                        <span class="font-bold text-slate-700 dark:text-slate-200">{{ $user['username'] }}</span>
                                    </td>
                                    <td class="p-4">
                                        <div class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $user['name'] ?? '-' }}</div>
                                        <div class="text-xs text-slate-500">{{ $user['email'] ?? '-' }}</div>
                                    </td>
                                    <td class="p-4">
                                        <span class="text-sm text-slate-600 dark:text-slate-400">{{ $user['package'] ?? '-' }}</span>
                                    </td>
                                    <td class="p-4">
                                        @if ($user['suspended'])
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">Suspended</span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Aktif</span>
                                        @endif
                                    </td>
                                    <td class="p-4">
                                        @if ($user['linked'])
                                            <div class="space-y-1">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                                    {{ $user['managed_by_crm'] ? 'Dikelola CRM' : 'Read-only' }}
                                                </span>
                                                <div class="text-xs text-slate-500">
                                                    @if ($user['subscription_id'])
                                                        <a href="{{ route('subscriptions.show', $user['subscription_id']) }}"
                                                            class="hover:text-blue-600 dark:hover:text-blue-400">#{{ $user['subscription_id'] }}</a>
                                                        @if ($user['domain'])
                                                            <span class="text-slate-400">· {{ $user['domain'] }}</span>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-xs text-slate-400">Belum tertaut</span>
                                        @endif
                                    </td>
                                    <td class="p-4 pr-6">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('servers.users.show', [$server, $user['username']]) }}" class="p-2 text-blue-600 transition-colors hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg" title="Detail user"><i data-lucide="eye" class="w-4 h-4"></i></a>
                                            @if ($user['linked'] && $user['lifecycle_available'])
                                                @can('servers.suspend')
                                                    @if ($user['suspended'])
                                                        <button data-username="{{ $user['username'] }}" onclick="window.confirmAction(this.dataset.username, '{{ route('servers.users.activate', $server) }}', 'Aktifkan akun ini?')"
                                                            class="p-2 hover:bg-green-50 dark:hover:bg-green-900/30 text-green-600 rounded-lg transition-colors" title="Aktifkan">
                                                            <i data-lucide="play" class="w-4 h-4"></i>
                                                        </button>
                                                    @else
                                                        <button data-username="{{ $user['username'] }}" onclick="window.confirmAction(this.dataset.username, '{{ route('servers.users.suspend', $server) }}', 'Nonaktifkan akun ini?')"
                                                            class="p-2 hover:bg-amber-50 dark:hover:bg-amber-900/30 text-amber-600 rounded-lg transition-colors" title="Suspend">
                                                            <i data-lucide="pause" class="w-4 h-4"></i>
                                                        </button>
                                                    @endif
                                                @endcan
                                                @can('servers.reset_password')
                                                    <button data-username="{{ $user['username'] }}" onclick="window.openPasswordModal(this.dataset.username, '{{ route('servers.users.password', $server) }}')"
                                                        class="p-2 hover:bg-blue-50 dark:hover:bg-blue-900/30 text-blue-600 rounded-lg transition-colors" title="Reset Password">
                                                        <i data-lucide="key-round" class="w-4 h-4"></i>
                                                    </button>
                                                @endcan
                                                @can('servers.delete_user')
                                                    <button data-username="{{ $user['username'] }}" onclick="window.openDeleteModal(this.dataset.username, '{{ route('servers.users.destroy', $server) }}')"
                                                        class="p-2 hover:bg-red-50 dark:hover:bg-red-900/30 text-red-600 rounded-lg transition-colors" title="Hapus User">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                @endcan
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-10 text-center text-slate-400">Tidak ada user ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Password Modal -->
    <div id="passwordModal" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="window.closePasswordModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-md">
                <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white">Reset Password</h3>
                    <button type="button" onclick="window.closePasswordModal()"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
                <form id="passwordForm" method="POST">
                    @csrf
                    <input type="hidden" id="passwordUsernameInput" name="username">
                    <div class="p-6 space-y-4">
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Masukkan password baru untuk akun <span id="passwordUsername"
                                class="font-bold text-slate-700 dark:text-slate-200"></span>.
                        </p>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Password Baru</label>
                            <input type="password" id="passwordInput" name="password" required minlength="8"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>
                    </div>
                    <div class="p-6 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3">
                        <button type="button" onclick="window.closePasswordModal()"
                            class="px-5 py-2.5 rounded-xl font-bold text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Batal</button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl font-bold bg-blue-600 text-white hover:bg-blue-700 transition-all">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Destructive action requires an explicit, server-validated confirmation. -->
    <div id="deleteModal" class="fixed inset-0 z-[70] hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="window.closeDeleteModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-md">
                <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-red-600 dark:text-red-400">Hapus Akun Hosting</h3>
                    <button type="button" onclick="window.closeDeleteModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" id="deleteUsernameInput" name="username">
                    <div class="p-6 space-y-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Seluruh web domain, database, cron, dan file milik akun <strong id="deleteUsername"></strong> akan dihapus permanen dari HestiaCP.
                        </p>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Ketik username untuk konfirmasi</label>
                            <input type="text" id="deleteConfirmation" name="confirmation" required autocomplete="off"
                                class="w-full rounded-xl border border-red-200 dark:border-red-900 px-4 py-2.5 bg-red-50/50 dark:bg-red-900/10 text-slate-800 dark:text-white focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all">
                        </div>
                    </div>
                    <div class="p-6 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3">
                        <button type="button" onclick="window.closeDeleteModal()" class="px-5 py-2.5 rounded-xl font-bold text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl font-bold bg-red-600 text-white hover:bg-red-700 transition-all">Hapus Permanen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-confirm-modal />

    <form id="actionForm" method="POST" class="hidden"></form>

    @push('scripts')
        <script>
            (function () {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                const actionForm = document.getElementById('actionForm');

                window.confirmAction = function (username, url, message, method = 'POST') {
                    showConfirmModal('Konfirmasi', message, () => {
                        actionForm.setAttribute('action', url);
                        actionForm.replaceChildren();

                        const tokenInput = document.createElement('input');
                        tokenInput.type = 'hidden';
                        tokenInput.name = '_token';
                        tokenInput.value = csrfToken;
                        actionForm.appendChild(tokenInput);

                        const usernameInput = document.createElement('input');
                        usernameInput.type = 'hidden';
                        usernameInput.name = 'username';
                        usernameInput.value = username;
                        actionForm.appendChild(usernameInput);
                        actionForm.submit();
                    });
                };

                window.openPasswordModal = function (username, url) {
                    document.getElementById('passwordUsername').innerText = username;
                    document.getElementById('passwordUsernameInput').value = username;
                    document.getElementById('passwordForm').setAttribute('action', url);
                    document.getElementById('passwordInput').value = '';
                    document.getElementById('passwordModal').classList.remove('hidden');
                };

                window.closePasswordModal = function () {
                    document.getElementById('passwordModal').classList.add('hidden');
                };

                window.openDeleteModal = function (username, url) {
                    document.getElementById('deleteUsername').innerText = username;
                    document.getElementById('deleteUsernameInput').value = username;
                    document.getElementById('deleteConfirmation').value = '';
                    document.getElementById('deleteForm').setAttribute('action', url);
                    document.getElementById('deleteModal').classList.remove('hidden');
                };

                window.closeDeleteModal = function () {
                    document.getElementById('deleteModal').classList.add('hidden');
                };
            })();
        </script>
    @endpush
</x-app-layout>
