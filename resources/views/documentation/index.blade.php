<x-app-layout>
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[320px_minmax(0,1fr)]">
        <aside class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800 xl:sticky xl:top-24 xl:max-h-[calc(100vh-7rem)]">
            <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-700">
                <h1 class="text-xl font-bold text-slate-900 dark:text-white">Dokumentasi</h1>
                <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">
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
                                        ? 'border-blue-200 bg-blue-50/80 text-blue-700 shadow-sm dark:border-blue-700 dark:bg-blue-900/20 dark:text-blue-300'
                                        : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700/40' }}">
                                    <div class="text-sm font-semibold leading-6">{{ $doc['title'] }}</div>
                                    @if($doc['excerpt'])
                                        <div class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500 dark:text-slate-400">
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

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
            @if($activeDoc)
                <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-5 dark:border-slate-700 dark:bg-slate-900/30">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <div class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $activeDoc['group'] }}</div>
                            <h2 class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ $activeDoc['title'] }}</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $activeDoc['path'] }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-xs leading-5 text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400">
                            Markdown reader internal.
                            <div class="font-medium text-slate-700 dark:text-slate-200">Sumber: folder <code class="rounded bg-slate-100 px-1.5 py-0.5 text-[11px] dark:bg-slate-700">docs/</code></div>
                        </div>
                    </div>
                </div>
                <article class="docs-content max-w-none px-6 py-8 text-[15px] leading-7 text-slate-600 dark:text-slate-300
                    [&_h1]:mt-0 [&_h1]:text-3xl [&_h1]:font-bold [&_h1]:tracking-tight [&_h1]:text-slate-900 dark:[&_h1]:text-white
                    [&_h2]:mt-10 [&_h2]:border-b [&_h2]:border-slate-200 [&_h2]:pb-3 [&_h2]:text-2xl [&_h2]:font-bold [&_h2]:text-slate-900 dark:[&_h2]:border-slate-700 dark:[&_h2]:text-white
                    [&_h3]:mt-8 [&_h3]:text-xl [&_h3]:font-semibold [&_h3]:text-slate-900 dark:[&_h3]:text-white
                    [&_h4]:mt-6 [&_h4]:text-base [&_h4]:font-semibold [&_h4]:uppercase [&_h4]:tracking-wide [&_h4]:text-slate-700 dark:[&_h4]:text-slate-200
                    [&_p]:my-4 [&_p]:leading-7
                    [&_ul]:my-4 [&_ul]:list-disc [&_ul]:space-y-2 [&_ul]:pl-6
                    [&_ol]:my-4 [&_ol]:list-decimal [&_ol]:space-y-2 [&_ol]:pl-6
                    [&_li]:pl-1
                    [&_strong]:font-semibold [&_strong]:text-slate-900 dark:[&_strong]:text-white
                    [&_a]:font-medium [&_a]:text-blue-600 [&_a]:underline [&_a]:underline-offset-4 hover:[&_a]:text-blue-700 dark:[&_a]:text-blue-300
                    [&_hr]:my-8 [&_hr]:border-slate-200 dark:[&_hr]:border-slate-700
                    [&_blockquote]:my-6 [&_blockquote]:rounded-2xl [&_blockquote]:border-l-4 [&_blockquote]:border-blue-500 [&_blockquote]:bg-blue-50/70 [&_blockquote]:px-5 [&_blockquote]:py-4 [&_blockquote]:text-slate-700 dark:[&_blockquote]:bg-blue-900/20 dark:[&_blockquote]:text-slate-200
                    [&_table]:my-6 [&_table]:w-full [&_table]:overflow-hidden [&_table]:rounded-2xl [&_table]:border [&_table]:border-slate-200 dark:[&_table]:border-slate-700
                    [&_thead]:bg-slate-50 dark:[&_thead]:bg-slate-900/30
                    [&_th]:border-b [&_th]:border-slate-200 [&_th]:px-4 [&_th]:py-3 [&_th]:text-left [&_th]:text-xs [&_th]:font-bold [&_th]:uppercase [&_th]:tracking-wider [&_th]:text-slate-500 dark:[&_th]:border-slate-700
                    [&_td]:border-b [&_td]:border-slate-100 [&_td]:px-4 [&_td]:py-3 [&_td]:align-top dark:[&_td]:border-slate-800
                    [&_tr:last-child_td]:border-b-0
                    [&_pre]:my-6 [&_pre]:overflow-x-auto [&_pre]:rounded-2xl [&_pre]:border [&_pre]:border-slate-200 [&_pre]:bg-slate-950 [&_pre]:px-5 [&_pre]:py-4 [&_pre]:text-[13px] [&_pre]:leading-6 [&_pre]:text-slate-100 dark:[&_pre]:border-slate-700
                    [&_pre_code]:bg-transparent [&_pre_code]:p-0 [&_pre_code]:text-slate-100
                    [&_code]:rounded-md [&_code]:bg-slate-100 [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:font-mono [&_code]:text-[13px] [&_code]:font-medium [&_code]:text-blue-700 dark:[&_code]:bg-slate-700 dark:[&_code]:text-blue-300
                    [&_pre_code:before]:content-none [&_pre_code:after]:content-none [&_code:before]:content-none [&_code:after]:content-none">
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
