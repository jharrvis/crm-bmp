<x-app-layout>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <a href="{{ route('metro-ethernets.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-300">
                    <i data-lucide="arrow-left" class="h-4 w-4"></i>
                    Kembali ke Metro Ethernet
                </a>
                <h1 class="mt-3 text-2xl font-bold text-slate-800 dark:text-white">{{ $metroEthernet->display_name }}</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Detail koneksi Metro Ethernet dan parameter teknisnya.</p>
            </div>
            <a href="{{ route('metro-ethernets.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-blue-500/40 dark:hover:bg-blue-900/20 dark:hover:text-blue-300">
                <i data-lucide="settings-2" class="h-4 w-4"></i>
                Kelola Metro Ethernet
            </a>
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <div class="border-b border-slate-100 px-6 py-5 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-300">
                        <i data-lucide="network" class="h-5 w-5"></i>
                    </div>
                    <div>
                        <h2 class="font-bold text-slate-800 dark:text-white">Informasi Koneksi</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Data layanan dari vendor.</p>
                    </div>
                </div>
            </div>

            <dl class="grid gap-x-6 gap-y-5 p-6 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Nama Koneksi</dt>
                    <dd class="mt-1 font-semibold text-slate-800 dark:text-slate-100">{{ $metroEthernet->display_name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Vendor</dt>
                    <dd class="mt-1 font-semibold text-slate-800 dark:text-slate-100">{{ $metroEthernet->vendor?->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">CID</dt>
                    <dd class="mt-1 font-mono font-semibold text-slate-800 dark:text-slate-100">{{ $metroEthernet->cid ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">IP Address</dt>
                    <dd class="mt-1 font-mono font-semibold text-slate-800 dark:text-slate-100">{{ $metroEthernet->ip_address ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Bandwidth</dt>
                    <dd class="mt-1 font-semibold text-slate-800 dark:text-slate-100">{{ $metroEthernet->bandwidth ? number_format($metroEthernet->bandwidth).' Mbps' : '-' }}</dd>
                </div>
            </dl>
        </section>
    </div>
</x-app-layout>
