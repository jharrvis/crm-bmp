<x-app-layout>
    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-6 py-6 md:px-8 border-b border-slate-200 dark:border-slate-700">
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Pembaruan Sistem</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Ringkasan fitur baru, perubahan penting, dan commit terbaru aplikasi.
                </p>
            </div>

            <div class="px-6 py-6 md:px-8 grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)]">
                <div class="space-y-5">
                    @forelse($entries as $entry)
                        <section class="rounded-[1.5rem] border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/30 p-5">
                            <div class="flex items-center justify-between gap-3">
                                <h2 class="text-lg font-bold text-slate-800 dark:text-white">{{ $entry['title'] }}</h2>
                                <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                    Release Note
                                </span>
                            </div>

                            <div class="mt-4 space-y-4">
                                @foreach($entry['sections'] as $sectionTitle => $items)
                                    <div>
                                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ $sectionTitle }}</h3>
                                        <ul class="mt-2 space-y-2 text-sm text-slate-600 dark:text-slate-300">
                                            @foreach($items as $item)
                                                <li class="flex gap-3">
                                                    <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-blue-500"></span>
                                                    <span>{{ $item }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @empty
                        <div class="rounded-[1.5rem] border border-dashed border-slate-300 dark:border-slate-700 p-8 text-center text-sm text-slate-500 dark:text-slate-400">
                            Belum ada data changelog yang bisa ditampilkan.
                        </div>
                    @endforelse
                </div>

                <aside class="space-y-5">
                    <section class="rounded-[1.5rem] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                        <h2 class="text-base font-bold text-slate-800 dark:text-white">Commit Terbaru</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Diambil dari repo lokal aplikasi.</p>

                        <div class="mt-4 space-y-3">
                            @forelse($recentCommits as $commit)
                                <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="font-mono text-xs font-bold text-blue-600 dark:text-blue-300">{{ $commit['short_hash'] }}</span>
                                        <span class="text-xs text-slate-400">{{ $commit['date'] }}</span>
                                    </div>
                                    <p class="mt-2 text-sm font-medium text-slate-700 dark:text-slate-200">{{ $commit['subject'] }}</p>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500 dark:text-slate-400">Commit repo tidak tersedia di environment ini.</p>
                            @endforelse
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>
