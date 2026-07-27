<x-app-layout>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <a href="{{ route('routers.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-300">
                    <i data-lucide="arrow-left" class="h-4 w-4"></i>
                    Kembali ke Router
                </a>
                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-white">{{ $router->name }}</h1>
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold {{ $router->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' }}">
                        {{ $router->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Detail perangkat dan penggunaan langganan.</p>
            </div>

            @can('routers.update')
                <a href="{{ route('routers.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-blue-500/40 dark:hover:bg-blue-900/20 dark:hover:text-blue-300">
                    <i data-lucide="settings-2" class="h-4 w-4"></i>
                    Kelola Router
                </a>
            @endcan
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <section class="xl:col-span-2 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="border-b border-slate-100 px-6 py-5 dark:border-slate-700">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-300">
                            <i data-lucide="router" class="h-5 w-5"></i>
                        </div>
                        <div>
                            <h2 class="font-bold text-slate-800 dark:text-white">Informasi Router</h2>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Konfigurasi koneksi perangkat.</p>
                        </div>
                    </div>
                </div>

                <dl class="grid gap-x-6 gap-y-5 p-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Host / IP</dt>
                        <dd class="mt-1 font-semibold text-slate-800 dark:text-slate-100">{{ $router->host }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Port API</dt>
                        <dd class="mt-1 font-semibold text-slate-800 dark:text-slate-100">{{ $router->port }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Cabang</dt>
                        <dd class="mt-1 font-semibold text-slate-800 dark:text-slate-100">{{ $router->branch?->name ?? 'Belum ditentukan' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Tipe</dt>
                        <dd class="mt-1 font-semibold capitalize text-slate-800 dark:text-slate-100">{{ $router->type ?? 'MikroTik' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Deskripsi</dt>
                        <dd class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-700 dark:text-slate-300">{{ $router->description ?: 'Tidak ada deskripsi.' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="border-b border-slate-100 px-6 py-5 dark:border-slate-700">
                    <h2 class="font-bold text-slate-800 dark:text-white">Kredensial Akses</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Hanya untuk kebutuhan operasional.</p>
                </div>
                <div class="space-y-5 p-6">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Username</p>
                        <p class="mt-1 break-all font-semibold text-slate-800 dark:text-slate-100">{{ $router->user }}</p>
                    </div>
                    <div>
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Password</p>
                            <div class="flex items-center gap-1">
                                <button type="button" id="toggleRouterPasswordButton" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-200" title="Tampilkan password" aria-label="Tampilkan password">
                                    <i data-lucide="eye" class="h-4 w-4"></i>
                                </button>
                                <button type="button" id="copyRouterPasswordButton" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-200" title="Salin password" aria-label="Salin password">
                                    <i data-lucide="copy" class="h-4 w-4"></i>
                                </button>
                            </div>
                        </div>
                        <input id="routerPassword" type="password" value="{{ $router->password }}" readonly autocomplete="off"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 font-mono text-sm text-slate-800 outline-none dark:border-slate-600 dark:bg-slate-700/50 dark:text-white">
                    </div>
                </div>
            </section>
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <div class="flex flex-col gap-3 border-b border-slate-100 px-6 py-5 dark:border-slate-700 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-bold text-slate-800 dark:text-white">Langganan Terkait</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Langganan koneksi yang menggunakan router ini.</p>
                </div>
                <span class="inline-flex w-fit items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:bg-blue-900/20 dark:text-blue-300">{{ $connectivities->total() }} langganan</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left">
                    <thead class="border-b border-slate-100 bg-slate-50/70 text-xs font-bold uppercase tracking-wide text-slate-400 dark:border-slate-700 dark:bg-slate-700/20">
                        <tr>
                            <th class="px-6 py-3">Langganan</th>
                            <th class="px-6 py-3">Pelanggan</th>
                            <th class="px-6 py-3">Paket</th>
                            <th class="px-6 py-3">IP / PPPoE</th>
                            <th class="px-6 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse ($connectivities as $connectivity)
                            @php($subscription = $connectivity->subscription)
                            <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                <td class="px-6 py-4">
                                    @if ($subscription)
                                        @can('subscriptions.view')
                                            <a href="{{ route('subscriptions.show', $subscription) }}" class="font-semibold text-slate-800 hover:text-blue-600 dark:text-slate-100 dark:hover:text-blue-300">{{ $subscription->subscription_code }}</a>
                                        @else
                                            <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $subscription->subscription_code }}</span>
                                        @endcan
                                    @else
                                        <span class="text-slate-400">Langganan tidak tersedia</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300">{{ $subscription?->client?->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300">{{ $subscription?->package?->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300">
                                    <div>{{ $connectivity->ip_address ?? '-' }}</div>
                                    @if ($connectivity->pppoe_user)
                                        <div class="mt-1 text-xs text-slate-400">{{ $connectivity->pppoe_user }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold {{ $subscription?->status === 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' }}">{{ $subscription?->status ? ucfirst(str_replace('_', ' ', $subscription->status)) : '-' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500 dark:text-slate-400">Belum ada langganan yang menggunakan router ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($connectivities->hasPages())
                <div class="border-t border-slate-100 px-6 py-4 dark:border-slate-700">{{ $connectivities->links() }}</div>
            @endif
        </section>
    </div>

    @push('scripts')
        <script>
            (() => {
                const passwordInput = document.getElementById('routerPassword');
                const toggleButton = document.getElementById('toggleRouterPasswordButton');

                toggleButton?.addEventListener('click', () => {
                    const isHidden = passwordInput.type === 'password';
                    passwordInput.type = isHidden ? 'text' : 'password';
                    toggleButton.title = isHidden ? 'Sembunyikan password' : 'Tampilkan password';
                    toggleButton.setAttribute('aria-label', toggleButton.title);
                    toggleButton.innerHTML = `<i data-lucide="${isHidden ? 'eye-off' : 'eye'}" class="h-4 w-4"></i>`;
                    lucide.createIcons();
                });

                document.getElementById('copyRouterPasswordButton')?.addEventListener('click', async () => {
                    try {
                        await navigator.clipboard.writeText(passwordInput.value);
                        showToast('Password router berhasil disalin.');
                    } catch (error) {
                        showToast('Gagal menyalin password router.', 'error');
                    }
                });
            })();
        </script>
    @endpush
</x-app-layout>
