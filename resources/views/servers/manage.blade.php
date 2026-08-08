<x-app-layout>
    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6 md:p-8">

            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <div>
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white">Manage Server: {{ $server->name }}</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">{{ $server->host }}:{{ $server->port }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <button onclick="window.testConnection()" id="testConnectionBtn"
                        class="flex items-center gap-2 border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 px-5 py-2.5 rounded-xl font-bold transition-colors">
                        <i data-lucide="activity" class="w-5 h-5"></i>
                        <span id="testConnectionText">Test Koneksi</span>
                    </button>
                    <form method="POST" action="{{ route('servers.refresh', $server) }}">
                        @csrf
                        <button type="submit"
                            class="flex items-center gap-2 border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 px-5 py-2.5 rounded-xl font-bold transition-colors">
                            <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                            Refresh Data
                        </button>
                    </form>
                    <a href="{{ route('servers.users', $server) }}"
                        class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-200 dark:shadow-none transition-all">
                        <i data-lucide="users" class="w-5 h-5"></i>
                        Daftar User
                    </a>
                </div>
            </div>

            <!-- Snapshot Status -->
            <div class="mb-8">
                @if ($snapshot)
                    <div class="flex flex-wrap items-center gap-3 text-sm">
                        @if ($snapshot->status === 'synced')
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                <i data-lucide="check-circle-2" class="w-4 h-4 mr-1"></i> Data tersinkron
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                <i data-lucide="alert-circle" class="w-4 h-4 mr-1"></i> Gagal tersinkron
                            </span>
                        @endif
                        <span class="text-slate-500 dark:text-slate-400">
                            Terakhir diperbarui: {{ $snapshot->last_synced_at?->diffForHumans() ?? '-' }}
                        </span>
                        @if ($snapshot->error_message)
                            <span class="text-red-500 text-xs">{{ $snapshot->error_message }}</span>
                        @endif
                    </div>
                @else
                    <div class="flex items-center gap-3 text-sm text-amber-600 dark:text-amber-400">
                        <i data-lucide="info" class="w-4 h-4"></i>
                        Belum ada snapshot. Klik Refresh Data untuk menarik ringkasan dari server.
                    </div>
                @endif
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-slate-50 dark:bg-slate-700/40 rounded-2xl p-5">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total User</p>
                    <p class="text-3xl font-bold text-slate-800 dark:text-white mt-2">{{ $snapshot?->summary_json['total_users'] ?? '-' }}</p>
                </div>
                <div class="bg-slate-50 dark:bg-slate-700/40 rounded-2xl p-5">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">User Terhubung</p>
                    <p class="text-3xl font-bold text-slate-800 dark:text-white mt-2">{{ $snapshot?->summary_json['linked_users'] ?? '-' }}</p>
                </div>
                <div class="bg-slate-50 dark:bg-slate-700/40 rounded-2xl p-5">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kapasitas</p>
                    <p class="text-3xl font-bold text-slate-800 dark:text-white mt-2">{{ $server->max_accounts > 0 ? $server->max_accounts : 'Unlimited' }}</p>
                </div>
                <div class="bg-slate-50 dark:bg-slate-700/40 rounded-2xl p-5">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">User Suspended</p>
                    <p class="text-3xl font-bold text-slate-800 dark:text-white mt-2">{{ $snapshot?->summary_json['suspended_users'] ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Link Existing User -->
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6 md:p-8">
            <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-1">Tautkan User Existing</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">
                Tautkan user yang sudah ada di HestiaCP ke subscription hosting. Akun tertaut bersifat read-only untuk aksi lifecycle.
            </p>

            <form method="POST" action="{{ route('servers.users.link', $server) }}" class="flex flex-col md:flex-row gap-4">
                @csrf
                <div class="flex-1">
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Username Hestia</label>
                    <input type="text" name="username" required
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                        placeholder="Contoh: client01" pattern="[a-zA-Z0-9_]{1,32}">
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Subscription</label>
                    <select name="subscription_id" required
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        <option value="">Pilih subscription hosting...</option>
                        @forelse ($linkableSubscriptions as $subscription)
                            <option value="{{ $subscription->id }}">
                                {{ $subscription->subscription_code }} - {{ $subscription->client?->name }} ({{ $subscription->package?->name }})
                            </option>
                        @empty
                            <option value="" disabled>Tidak ada subscription hosting yang tersedia</option>
                        @endforelse
                    </select>
                </div>
                <div class="md:pt-8">
                    <button type="submit"
                        class="w-full md:w-auto flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-200 dark:shadow-none transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        {{ $linkableSubscriptions->isEmpty() ? 'disabled' : '' }}>
                        <i data-lucide="link" class="w-5 h-5"></i>
                        Tautkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            (function () {
                const baseUrl = '{{ url('/') }}';
                const testUrl = '{{ route('servers.test-connection', $server) }}';

                window.testConnection = function () {
                    const btn = document.getElementById('testConnectionBtn');
                    const spinner = document.getElementById('testConnectionSpinner');
                    const text = document.getElementById('testConnectionText');

                    btn.disabled = true;
                    if (spinner) spinner.classList.remove('hidden');
                    text.innerText = 'Menghubungi server...';

                    fetch(testUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })
                        .then(r => r.json())
                        .then(data => {
                            btn.disabled = false;
                            if (spinner) spinner.classList.add('hidden');
                            text.innerText = 'Test Koneksi';
                            showToast(data.message || 'Selesai', data.success ? 'success' : 'error');
                        })
                        .catch(() => {
                            btn.disabled = false;
                            if (spinner) spinner.classList.add('hidden');
                            text.innerText = 'Test Koneksi';
                            showToast('Terjadi kesalahan saat menghubungi server.', 'error');
                        });
                };
            })();
        </script>
    @endpush
</x-app-layout>
