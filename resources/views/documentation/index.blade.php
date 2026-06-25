<x-app-layout>
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[320px_minmax(0,1fr)]">
        <aside class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700">
                <h1 class="text-xl font-bold text-slate-900 dark:text-white">Dokumentasi</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Referensi modul, API, deployment, dan standar pengembangan.
                </p>
            </div>

            <div class="max-h-[calc(100vh-14rem)] overflow-y-auto px-4 py-4">
                @forelse($docs as $group => $groupDocs)
                    <div class="mb-6">
                        <h2 class="px-2 text-[11px] font-bold uppercase tracking-widest text-slate-400">{{ $group }}</h2>
                        <div class="mt-3 space-y-2">
                            @foreach($groupDocs as $doc)
                                <a href="{{ route('documentation.index', ['doc' => $doc['path']]) }}"
                                    class="block rounded-xl border px-4 py-3 transition-all {{ $activeDoc && $activeDoc['path'] === $doc['path']
                                        ? 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-700 dark:bg-blue-900/20 dark:text-blue-300'
                                        : 'border-slate-200 bg-white hover:bg-slate-50 text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700/40' }}">
                                    <div class="text-sm font-semibold">{{ $doc['title'] }}</div>
                                    @if($doc['excerpt'])
                                        <div class="mt-1 line-clamp-2 text-xs text-slate-500 dark:text-slate-400">
                                            {{ $doc['excerpt'] }}
                                        </div>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-600 dark:text-slate-400">
                        Belum ada dokumen di folder <code>docs/</code>.
                    </div>
                @endforelse
            </div>
        </aside>

        <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            @if($activeDoc)
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700">
                    <div class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $activeDoc['group'] }}</div>
                    <h2 class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ $activeDoc['title'] }}</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $activeDoc['path'] }}</p>
                </div>
                <article class="prose prose-slate max-w-none px-6 py-6 dark:prose-invert prose-headings:font-bold prose-pre:rounded-xl prose-code:text-blue-600 dark:prose-code:text-blue-300">
                    {!! $content !!}
                </article>
            @else
                <div class="px-6 py-10 text-center text-slate-500 dark:text-slate-400">
                    Pilih dokumen dari panel kiri.
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
