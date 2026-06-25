<x-app-layout>
    @php
        $moduleOptions = [
            'all' => 'Semua Modul',
            'branches' => 'Cabang',
            'divisions' => 'Divisi',
            'employees' => 'Karyawan',
            'clients' => 'Pelanggan',
            'subscriptions' => 'Langganan',
            'invoices' => 'Invoice',
            'tickets' => 'Tiket',
            'routers' => 'Router',
            'servers' => 'Server Hosting',
            'vendors' => 'Vendor',
            'metro_ethernets' => 'Metro Ethernet',
            'services' => 'Layanan',
            'packages' => 'Paket',
        ];
    @endphp

    <div class="space-y-6">
        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800 md:p-8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Hasil Pencarian Global</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Cari lintas modul operasional tanpa harus berpindah menu satu per satu.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <div class="rounded-2xl bg-blue-50 px-4 py-3 dark:bg-blue-900/20">
                        <div class="text-[11px] font-bold uppercase tracking-widest text-blue-500">Query</div>
                        <div class="mt-1 text-sm font-semibold text-blue-700 dark:text-blue-300">
                            {{ $query !== '' ? $query : 'Belum ada query' }}
                        </div>
                    </div>
                    <div class="rounded-2xl bg-slate-100 px-4 py-3 dark:bg-slate-700/60">
                        <div class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Total Match</div>
                        <div class="mt-1 text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $total }}</div>
                    </div>
                </div>
            </div>

            <form action="{{ route('search.results') }}" method="GET" class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1fr)_220px_auto]">
                <div class="relative">
                    <i data-lucide="search" class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"></i>
                    <input
                        type="text"
                        name="q"
                        value="{{ $query }}"
                        placeholder="Cari pelanggan, invoice, tiket, langganan, perangkat, atau layanan"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-12 pr-4 text-sm text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-slate-600 dark:bg-slate-700/50 dark:text-slate-100 dark:focus:bg-slate-800"
                    >
                </div>

                <select
                    name="module"
                    class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100">
                    @foreach ($moduleOptions as $value => $label)
                        <option value="{{ $value }}" @selected($selectedModule === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-blue-700">
                    <i data-lucide="search-check" class="h-4 w-4"></i>
                    Tampilkan
                </button>
            </form>
        </div>

        @if ($query === '')
            <div class="rounded-[2rem] border border-dashed border-slate-300 bg-white px-6 py-12 text-center shadow-sm dark:border-slate-600 dark:bg-slate-800">
                <i data-lucide="search" class="mx-auto h-10 w-10 text-slate-300 dark:text-slate-500"></i>
                <h2 class="mt-4 text-lg font-bold text-slate-700 dark:text-slate-100">Masukkan kata kunci pencarian</h2>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Gunakan minimal 2 karakter untuk mulai mencari lintas modul.
                </p>
            </div>
        @elseif (empty($groups))
            <div class="rounded-[2rem] border border-dashed border-slate-300 bg-white px-6 py-12 text-center shadow-sm dark:border-slate-600 dark:bg-slate-800">
                <i data-lucide="search-x" class="mx-auto h-10 w-10 text-slate-300 dark:text-slate-500"></i>
                <h2 class="mt-4 text-lg font-bold text-slate-700 dark:text-slate-100">Tidak ada hasil ditemukan</h2>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Coba kata kunci lain atau ubah filter modul agar cakupannya lebih luas.
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-6">
                @foreach ($groups as $group)
                    <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                        <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-700 md:px-8">
                            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-300">
                                        <i data-lucide="{{ $group['icon'] }}" class="h-5 w-5"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-bold text-slate-800 dark:text-white">{{ $group['group'] }}</h2>
                                        <p class="text-sm text-slate-500 dark:text-slate-400">
                                            {{ $group['count'] }} hasil yang cocok
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="divide-y divide-slate-100 dark:divide-slate-700">
                            @foreach ($group['items'] as $item)
                                <a
                                    href="{{ $item['url'] }}"
                                    class="flex flex-col gap-3 px-6 py-4 transition hover:bg-slate-50 dark:hover:bg-slate-700/40 md:flex-row md:items-center md:justify-between md:px-8">
                                    <div class="min-w-0">
                                        <div class="truncate text-sm font-semibold text-slate-800 dark:text-slate-100">
                                            {{ $item['title'] }}
                                        </div>
                                        @if (!empty($item['subtitle']))
                                            <div class="mt-1 truncate text-sm text-slate-500 dark:text-slate-400">
                                                {{ $item['subtitle'] }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-3 self-start md:self-center">
                                        @if (!empty($item['badge']))
                                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold capitalize text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                                {{ $item['badge'] }}
                                            </span>
                                        @endif
                                        <i data-lucide="arrow-up-right" class="h-4 w-4 text-slate-300 transition group-hover:text-blue-500"></i>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
