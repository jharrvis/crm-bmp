@props(['items' => []])

@if (!empty($items))
    <nav aria-label="Breadcrumb"
        class="rounded-2xl border border-slate-200/80 bg-white/90 px-4 py-3 shadow-sm backdrop-blur dark:border-slate-700 dark:bg-slate-800/90 md:px-5">
        <ol class="flex flex-wrap items-center gap-y-2 text-sm">
            @foreach ($items as $item)
                <li class="flex items-center gap-2 text-slate-400 dark:text-slate-500">
                    @if (!$loop->first)
                        <i data-lucide="chevron-right" class="h-4 w-4 shrink-0"></i>
                    @endif

                    @if (!empty($item['url']) && empty($item['current']))
                        <a href="{{ $item['url'] }}"
                            class="inline-flex items-center rounded-lg px-1.5 py-0.5 font-medium text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-100">
                            {{ $item['label'] }}
                        </a>
                    @elseif (empty($item['current']))
                        <span class="px-1.5 py-0.5 font-medium text-slate-500 dark:text-slate-400">
                            {{ $item['label'] }}
                        </span>
                    @else
                        <span
                            class="inline-flex items-center rounded-lg bg-blue-50 px-2.5 py-1 text-sm font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                            {{ $item['label'] }}
                        </span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
