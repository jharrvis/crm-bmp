<x-app-layout>
    @php
        $statusClasses = [
            'open' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
            'in_progress' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
            'waiting_client' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
            'resolved' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
            'closed' => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
        ];

        $priorityClasses = [
            'low' => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
            'normal' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
            'high' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
            'urgent' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
        ];

        $advancedFiltersOpen = request()->filled('category')
            || request()->filled('client_id')
            || request()->filled('assigned_to')
            || request()->filled('date_from')
            || request()->filled('date_to');

        $ticketViews = [
            'all' => ['label' => 'Semua', 'count' => $summaryCounts['total']],
            'need_response' => ['label' => 'Perlu Respon', 'count' => $summaryCounts['open'] + $summaryCounts['in_progress']],
            'urgent' => ['label' => 'Urgent', 'count' => $summaryCounts['urgent']],
            'unassigned' => ['label' => 'Unassigned', 'count' => $summaryCounts['unassigned']],
            'waiting_client' => ['label' => 'Waiting Client', 'count' => $summaryCounts['waiting_client']],
        ];
    @endphp

    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-6 py-6 md:px-8 border-b border-slate-200 dark:border-slate-700">
                <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5">
                    <div>
                        <h3 class="text-2xl font-bold text-slate-800 dark:text-white">Ticket Support</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
                            Kelola triage support dengan tampilan yang lebih fokus dan cepat dipindai.
                        </p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <span class="inline-flex items-center gap-2 rounded-full bg-blue-50 dark:bg-blue-900/20 px-3 py-1.5 text-xs font-bold text-blue-700 dark:text-blue-300">
                                <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                                Open {{ $summaryCounts['open'] }}
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-full bg-yellow-50 dark:bg-yellow-900/20 px-3 py-1.5 text-xs font-bold text-yellow-700 dark:text-yellow-300">
                                <span class="h-2 w-2 rounded-full bg-yellow-500"></span>
                                In Progress {{ $summaryCounts['in_progress'] }}
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-full bg-purple-50 dark:bg-purple-900/20 px-3 py-1.5 text-xs font-bold text-purple-700 dark:text-purple-300">
                                <span class="h-2 w-2 rounded-full bg-purple-500"></span>
                                Waiting {{ $summaryCounts['waiting_client'] }}
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-full bg-red-50 dark:bg-red-900/20 px-3 py-1.5 text-xs font-bold text-red-700 dark:text-red-300">
                                <span class="h-2 w-2 rounded-full bg-red-500"></span>
                                Urgent {{ $summaryCounts['urgent'] }}
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 dark:bg-amber-900/20 px-3 py-1.5 text-xs font-bold text-amber-700 dark:text-amber-300">
                                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                Unassigned {{ $summaryCounts['unassigned'] }}
                            </span>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('ticket-canned-responses.index') }}"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                            <i data-lucide="messages-square" class="w-4 h-4"></i>
                            Template Balasan
                        </a>
                        <button type="button"
                            data-modal-target="createTicketDrawer"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            Buat Ticket
                        </button>
                    </div>
                </div>
            </div>

            <form method="GET" class="px-6 py-5 md:px-8 space-y-4">
                <input type="hidden" name="view" value="{{ $view }}">

                <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                    <div class="inline-flex w-full xl:w-auto items-center rounded-2xl bg-slate-100 dark:bg-slate-700/60 p-1.5 overflow-x-auto no-scrollbar">
                        @foreach($ticketViews as $value => $item)
                            <a href="{{ route('tickets.index', array_merge(request()->except('page', 'view'), ['view' => $value])) }}"
                                class="inline-flex shrink-0 items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold transition-colors {{ $view === $value ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white' }}">
                                <span>{{ $item['label'] }}</span>
                                <span class="text-xs {{ $view === $value ? 'text-slate-400 dark:text-slate-400' : 'text-slate-400 dark:text-slate-500' }}">{{ $item['count'] }}</span>
                            </a>
                        @endforeach
                    </div>
                    <div class="flex flex-col md:flex-row gap-3 xl:min-w-[620px]">
                        <div class="relative flex-1">
                            <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                            <input type="text" name="q" value="{{ request('q') }}"
                                placeholder="Cari tiket, subjek, client, atau client code"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 pl-11 pr-4 py-3 bg-white dark:bg-slate-700/30 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <button type="button"
                            id="toggleAdvancedFilters"
                            class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700/30 text-slate-700 dark:text-slate-200 font-bold hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                            <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                            Filter
                        </button>
                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 transition-colors">
                            Terapkan
                        </button>
                    </div>
                </div>

                <div id="advancedFiltersPanel"
                    class="{{ $advancedFiltersOpen ? '' : 'hidden' }} rounded-[1.5rem] border border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-900/30 p-4 md:p-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-7 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Status</label>
                            <select name="status"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Semua</option>
                                @foreach(['open', 'in_progress', 'waiting_client', 'resolved', 'closed'] as $status)
                                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Queue</label>
                            <select name="queue"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Semua</option>
                                @foreach($ticketQueues as $value => $label)
                                    <option value="{{ $value }}" {{ request('queue') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Priority</label>
                            <select name="priority"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Semua</option>
                                @foreach(['low', 'normal', 'high', 'urgent'] as $priority)
                                    <option value="{{ $priority }}" {{ request('priority') === $priority ? 'selected' : '' }}>{{ ucfirst($priority) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Kategori</label>
                            <select name="category"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Semua Kategori</option>
                                @foreach(['connectivity', 'billing', 'technical', 'general'] as $category)
                                    <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>{{ ucfirst($category) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Client</label>
                            <select name="client_id"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Semua Client</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ (string) request('client_id') === (string) $client->id ? 'selected' : '' }}>
                                        {{ $client->name }} ({{ $client->client_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Assignee</label>
                            <select name="assigned_to"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Semua Assignee</option>
                                <option value="unassigned" {{ request('assigned_to') === 'unassigned' ? 'selected' : '' }}>Belum di-assign</option>
                                @foreach($staffUsers as $user)
                                    <option value="{{ $user->id }}" {{ (string) request('assigned_to') === (string) $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Dari Tanggal</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Sampai Tanggal</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ $tickets->count() }} tiket tampil</p>
                        <a href="{{ route('tickets.index', request()->only('view')) }}"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                            Reset Filter
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            @if($tickets->isEmpty())
                <div class="p-10 text-center text-slate-500 dark:text-slate-400">
                    Belum ada tiket support pada filter ini.
                </div>
            @else
                <form method="POST" action="{{ route('tickets.bulk-update') }}" id="bulkActionForm">
                    @csrf
                    <div id="bulkActionBar"
                        class="hidden sticky top-0 z-20 border-b border-slate-200 dark:border-slate-700 bg-white/95 dark:bg-slate-800/95 backdrop-blur px-4 md:px-6 py-4">
                        <div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-4">
                            <div>
                                <h4 class="text-sm font-bold uppercase tracking-widest text-slate-500">Bulk Actions</h4>
                                <p id="bulkSelectionCount" class="text-sm text-slate-600 dark:text-slate-300 mt-1">0 ticket dipilih</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 xl:min-w-[760px]">
                                <select name="status"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Status: Tidak diubah</option>
                                    @foreach(['open', 'in_progress', 'waiting_client', 'resolved', 'closed'] as $status)
                                        <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                    @endforeach
                                </select>
                                <select name="queue"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Queue: Tidak diubah</option>
                                    @foreach($ticketQueues as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <select name="priority"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Priority: Tidak diubah</option>
                                    @foreach(['low', 'normal', 'high', 'urgent'] as $priority)
                                        <option value="{{ $priority }}">{{ ucfirst($priority) }}</option>
                                    @endforeach
                                </select>
                                <select name="assigned_to"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Assign: Tidak diubah</option>
                                    <option value="__unassigned__">Unassigned</option>
                                    @foreach($staffUsers as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold bg-slate-900 dark:bg-blue-600 text-white hover:bg-slate-800 dark:hover:bg-blue-700 transition-colors">
                                    <i data-lucide="save" class="w-4 h-4"></i>
                                    Terapkan
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto no-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-700">
                                    <th class="p-4 pl-6 w-12">
                                        <input type="checkbox" id="bulkSelectAll"
                                            class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    </th>
                                    <th class="p-4">
                                        <button type="button" data-sort-button data-sort-key="ticket" data-sort-type="text"
                                            class="inline-flex items-center gap-2 text-left text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                                            <span>Ticket</span>
                                            <i data-lucide="arrow-up-down" class="w-3.5 h-3.5 sort-icon"></i>
                                        </button>
                                    </th>
                                    <th class="p-4">
                                        <button type="button" data-sort-button data-sort-key="subject" data-sort-type="text"
                                            class="inline-flex items-center gap-2 text-left text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                                            <span>Issue</span>
                                            <i data-lucide="arrow-up-down" class="w-3.5 h-3.5 sort-icon"></i>
                                        </button>
                                    </th>
                                    <th class="p-4">
                                        <button type="button" data-sort-button data-sort-key="created" data-sort-type="date"
                                            class="inline-flex items-center gap-2 text-left text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                                            <span>Created</span>
                                            <i data-lucide="arrow-up-down" class="w-3.5 h-3.5 sort-icon"></i>
                                        </button>
                                    </th>
                                    <th class="p-4">
                                        <button type="button" data-sort-button data-sort-key="status" data-sort-type="text"
                                            class="inline-flex items-center gap-2 text-left text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                                            <span>Status</span>
                                            <i data-lucide="arrow-up-down" class="w-3.5 h-3.5 sort-icon"></i>
                                        </button>
                                    </th>
                                    <th class="p-4 pr-6 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody id="ticketTableBody" class="divide-y divide-slate-100 dark:divide-slate-700">
                                @foreach($tickets as $ticket)
                                    <tr
                                        data-ticket="{{ strtolower($ticket->ticket_number) }}"
                                        data-client="{{ strtolower($ticket->client->name) }}"
                                        data-subject="{{ strtolower($ticket->subject) }}"
                                        data-created="{{ $ticket->created_at?->timestamp ?? 0 }}"
                                        data-status="{{ strtolower($ticket->status) }}"
                                        class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors {{ $ticket->priority === 'urgent' ? 'bg-red-50/40 dark:bg-red-900/5' : '' }}">
                                        <td class="p-4 pl-6 align-top">
                                            <input type="checkbox" name="ticket_ids[]" value="{{ $ticket->id }}"
                                                class="bulk-ticket-checkbox rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                        </td>
                                        <td class="p-4 align-top min-w-[250px]">
                                            <div class="font-mono font-bold text-slate-800 dark:text-white">{{ $ticket->ticket_number }}</div>
                                            <div class="mt-2 text-sm font-semibold text-slate-800 dark:text-white">{{ $ticket->client->name }}</div>
                                            <div class="mt-1 text-xs text-slate-500">{{ $ticket->client->client_code }}</div>
                                            <div class="mt-1 text-xs text-slate-500">{{ $ticket->client->primaryContact?->name ?? '-' }}</div>
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                @if(($ticket->unread_staff_replies_count ?? 0) > 0)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300">
                                                        {{ $ticket->unread_staff_replies_count }} baru
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="p-4 align-top min-w-[340px]">
                                            <div class="font-bold text-slate-800 dark:text-white leading-snug">{{ $ticket->subject }}</div>
                                            <div class="mt-2 max-w-[460px] overflow-hidden text-sm text-slate-500 dark:text-slate-400 max-h-11">
                                                {{ $ticket->message }}
                                            </div>
                                            <div class="mt-3 flex flex-wrap gap-2">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                                    {{ ucfirst($ticket->category) }}
                                                </span>
                                                @if($ticket->subscription)
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300">
                                                        {{ $ticket->subscription->subscription_code }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="p-4 align-top min-w-[150px]">
                                            <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                                                {{ $ticket->created_at?->format('d M Y') }}
                                            </div>
                                            <div class="mt-1 text-xs text-slate-500">
                                                {{ $ticket->created_at?->format('H:i') }}
                                            </div>
                                            <div class="mt-2 text-xs text-slate-500">
                                                Updated {{ $ticket->updated_at?->diffForHumans() }}
                                            </div>
                                        </td>
                                        <td class="p-4 align-top min-w-[240px]">
                                            <div class="flex flex-wrap gap-2">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300">
                                                    {{ $ticketQueues[$ticket->queue] ?? strtoupper($ticket->queue ?? '-') }}
                                                </span>
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $statusClasses[$ticket->status] ?? 'bg-slate-100 text-slate-700' }}">
                                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                                </span>
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $priorityClasses[$ticket->priority] ?? 'bg-slate-100 text-slate-700' }}">
                                                    {{ ucfirst($ticket->priority) }}
                                                </span>
                                                @if(is_null($ticket->assigned_to))
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                                                        Unassigned
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="mt-3 text-sm font-semibold text-slate-700 dark:text-slate-200">
                                                {{ $ticket->assignedUser?->name ?? 'Belum di-assign' }}
                                            </div>
                                            <div class="mt-1 text-xs text-slate-500">{{ $ticket->replies_count ?? 0 }} balasan</div>
                                        </td>
                                        <td class="p-4 pr-6 align-top">
                                            <div class="flex justify-end">
                                                <details class="relative group">
                                                    <summary class="list-none inline-flex items-center justify-center h-10 w-10 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer transition-colors">
                                                        <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                                                    </summary>
                                                    <div class="absolute right-0 top-12 z-20 w-44 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-xl p-2">
                                                        <a href="{{ route('tickets.show', $ticket) }}"
                                                            class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                                            View Detail
                                                        </a>
                                                        <button type="button"
                                                            data-modal-target="quickEditTicketModal-{{ $ticket->id }}"
                                                            class="w-full flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                                            <i data-lucide="square-pen" class="w-4 h-4"></i>
                                                            Quick Edit
                                                        </button>
                                                    </div>
                                                </details>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <div id="createTicketDrawer" data-modal-root class="fixed inset-0 z-[120] hidden bg-slate-950/60 backdrop-blur-sm">
        <div class="absolute inset-y-0 right-0 w-full max-w-3xl bg-white dark:bg-slate-800 border-l border-slate-200 dark:border-slate-700 shadow-2xl overflow-y-auto">
            <div class="sticky top-0 z-10 flex items-center justify-between gap-4 px-6 py-5 bg-white/95 dark:bg-slate-800/95 backdrop-blur border-b border-slate-200 dark:border-slate-700">
                <div>
                    <h4 class="text-lg font-bold text-slate-800 dark:text-white">Buat Ticket Baru</h4>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Gunakan jika client melapor lewat telepon, WhatsApp, atau belum bisa membuat tiket dari portal.</p>
                </div>
                <button type="button" data-modal-close class="rounded-xl p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data" class="p-6 space-y-5">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Client <span class="text-red-500">*</span></label>
                        <select id="client_id" name="client_id" required
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih client</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                    {{ $client->name }} ({{ $client->client_code }})
                                </option>
                            @endforeach
                        </select>
                        @error('client_id')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Layanan Terkait</label>
                        <select id="subscription_id" name="subscription_id"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih layanan jika ada</option>
                            @foreach($clients as $client)
                                @foreach($client->subscriptions as $subscription)
                                    <option value="{{ $subscription->id }}"
                                        data-client-id="{{ $client->id }}"
                                        {{ old('subscription_id') == $subscription->id ? 'selected' : '' }}>
                                        {{ $subscription->subscription_code }} | {{ $subscription->package->name ?? '-' }}
                                    </option>
                                @endforeach
                            @endforeach
                        </select>
                        @error('subscription_id')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Kategori <span class="text-red-500">*</span></label>
                        <select name="category" required
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach(['connectivity' => 'Connectivity', 'billing' => 'Billing', 'technical' => 'Technical', 'general' => 'General'] as $value => $label)
                                <option value="{{ $value }}" {{ old('category', 'technical') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Queue Department</label>
                        <select name="queue"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Otomatis dari kategori</option>
                            @foreach($ticketQueues as $value => $label)
                                <option value="{{ $value }}" {{ old('queue') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Priority <span class="text-red-500">*</span></label>
                        <select name="priority" required
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach(['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent'] as $value => $label)
                                <option value="{{ $value }}" {{ old('priority', 'normal') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Assign Ke</label>
                        <select name="assigned_to"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Belum di-assign</option>
                            @foreach($staffUsers as $user)
                                <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Subjek <span class="text-red-500">*</span></label>
                    <input type="text" name="subject" value="{{ old('subject') }}" required
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Contoh: Internet down di kantor cabang Salatiga">
                    @error('subject')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Pesan / Kronologi <span class="text-red-500">*</span></label>
                    <textarea name="message" rows="5" required
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-3 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Tulis ringkasan keluhan, kronologi, dan detail yang dilaporkan client.">{{ old('message') }}</textarea>
                    @error('message')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Lampiran</label>
                    <input type="file" name="attachments[]" multiple
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Maksimal 5MB per file. Format: JPG, PNG, PDF, DOC, DOCX, XLS, XLSX, ZIP, TXT.</p>
                    @error('attachments')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                    @error('attachments.*')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" data-modal-close
                        class="px-5 py-2.5 rounded-xl font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl font-bold bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                        Buat Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>

    @foreach($tickets as $ticket)
        <div id="quickEditTicketModal-{{ $ticket->id }}" data-modal-root class="fixed inset-0 z-[120] hidden items-center justify-center bg-slate-950/70 backdrop-blur-sm p-4">
            <div class="w-full max-w-2xl rounded-[2rem] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-2xl">
                <div class="flex items-center justify-between gap-4 px-6 py-5 border-b border-slate-200 dark:border-slate-700">
                    <div>
                        <h4 class="text-lg font-bold text-slate-800 dark:text-white">Quick Edit Ticket</h4>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $ticket->ticket_number }} • {{ $ticket->subject }}</p>
                    </div>
                    <button type="button" data-modal-close class="rounded-xl p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('tickets.update', $ticket) }}" class="p-6 space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Status</label>
                            <select name="status"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach(['open', 'in_progress', 'waiting_client', 'resolved', 'closed'] as $status)
                                    <option value="{{ $status }}" {{ $ticket->status === $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Priority</label>
                            <select name="priority"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach(['low', 'normal', 'high', 'urgent'] as $priority)
                                    <option value="{{ $priority }}" {{ $ticket->priority === $priority ? 'selected' : '' }}>{{ ucfirst($priority) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Queue Department</label>
                            <select name="queue"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Tidak dipilih</option>
                                @foreach($ticketQueues as $value => $label)
                                    <option value="{{ $value }}" {{ $ticket->queue === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Assign Staff</label>
                            <select name="assigned_to"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Belum di-assign</option>
                                @foreach($staffUsers as $user)
                                    <option value="{{ $user->id }}" {{ $ticket->assigned_to === $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" data-modal-close
                            class="px-5 py-2.5 rounded-xl font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl font-bold bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const clientSelect = document.getElementById('client_id');
                const subscriptionSelect = document.getElementById('subscription_id');
                const bulkSelectAll = document.getElementById('bulkSelectAll');
                const bulkCheckboxes = Array.from(document.querySelectorAll('.bulk-ticket-checkbox'));
                const bulkSelectionCount = document.getElementById('bulkSelectionCount');
                const bulkActionBar = document.getElementById('bulkActionBar');
                const advancedFiltersPanel = document.getElementById('advancedFiltersPanel');
                const toggleAdvancedFilters = document.getElementById('toggleAdvancedFilters');
                const ticketTableBody = document.getElementById('ticketTableBody');
                const sortButtons = Array.from(document.querySelectorAll('[data-sort-button]'));
                let currentSort = {
                    key: null,
                    direction: 'asc',
                };

                function openModal(modalId) {
                    const modal = document.getElementById(modalId);

                    if (!modal) {
                        return;
                    }

                    modal.classList.remove('hidden');

                    if (modal.id === 'createTicketDrawer') {
                        document.body.classList.add('overflow-hidden');
                        return;
                    }

                    modal.classList.add('flex');
                }

                function closeModal(modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');

                    if (modal.id === 'createTicketDrawer') {
                        document.body.classList.remove('overflow-hidden');
                    }
                }

                function filterSubscriptions() {
                    if (!clientSelect || !subscriptionSelect) {
                        return;
                    }

                    const selectedClientId = clientSelect.value;
                    let hasVisibleOption = false;

                    Array.from(subscriptionSelect.options).forEach((option, index) => {
                        if (index === 0) {
                            option.hidden = false;
                            return;
                        }

                        const optionClientId = option.getAttribute('data-client-id');
                        const shouldShow = !selectedClientId || optionClientId === selectedClientId;
                        option.hidden = !shouldShow;

                        if (!shouldShow && option.selected) {
                            subscriptionSelect.value = '';
                        }

                        if (shouldShow) {
                            hasVisibleOption = true;
                        }
                    });

                    if (!hasVisibleOption) {
                        subscriptionSelect.value = '';
                    }
                }

                function updateBulkSelectionCount() {
                    if (!bulkActionBar || !bulkSelectionCount) {
                        return;
                    }

                    const selectedCount = bulkCheckboxes.filter((checkbox) => checkbox.checked).length;
                    bulkSelectionCount.textContent = `${selectedCount} ticket dipilih`;

                    if (selectedCount > 0) {
                        bulkActionBar.classList.remove('hidden');
                    } else {
                        bulkActionBar.classList.add('hidden');
                    }
                }

                function updateSortIcons() {
                    sortButtons.forEach((button) => {
                        const icon = button.querySelector('.sort-icon');

                        if (!icon) {
                            return;
                        }

                        const isActive = button.dataset.sortKey === currentSort.key;
                        button.classList.toggle('text-slate-700', isActive);
                        button.classList.toggle('dark:text-slate-100', isActive);
                        button.classList.toggle('text-slate-400', !isActive);

                        icon.setAttribute(
                            'data-lucide',
                            !isActive ? 'arrow-up-down' : currentSort.direction === 'asc' ? 'arrow-up' : 'arrow-down'
                        );
                    });

                    if (window.lucide) {
                        window.lucide.createIcons();
                    }
                }

                function sortTicketRows(key, type, direction) {
                    if (!ticketTableBody) {
                        return;
                    }

                    const rows = Array.from(ticketTableBody.querySelectorAll('tr'));
                    const multiplier = direction === 'asc' ? 1 : -1;

                    rows.sort((a, b) => {
                        let valueA = a.dataset[key] ?? '';
                        let valueB = b.dataset[key] ?? '';

                        if (type === 'date' || type === 'number') {
                            valueA = Number(valueA);
                            valueB = Number(valueB);

                            return (valueA - valueB) * multiplier;
                        }

                        return valueA.localeCompare(valueB, 'id', { sensitivity: 'base' }) * multiplier;
                    });

                    rows.forEach((row) => ticketTableBody.appendChild(row));
                }

                document.querySelectorAll('[data-modal-target]').forEach((trigger) => {
                    trigger.addEventListener('click', function () {
                        openModal(trigger.getAttribute('data-modal-target'));
                    });
                });

                document.querySelectorAll('[data-modal-close]').forEach((trigger) => {
                    trigger.addEventListener('click', function () {
                        const modal = trigger.closest('[data-modal-root]');

                        if (modal) {
                            closeModal(modal);
                        }
                    });
                });

                document.querySelectorAll('[data-modal-root]').forEach((modal) => {
                    modal.addEventListener('click', function (event) {
                        if (event.target === modal) {
                            closeModal(modal);
                        }
                    });
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key !== 'Escape') {
                        return;
                    }

                    document.querySelectorAll('[data-modal-root]').forEach((modal) => {
                        if (!modal.classList.contains('hidden')) {
                            closeModal(modal);
                        }
                    });
                });

                if (clientSelect && subscriptionSelect) {
                    clientSelect.addEventListener('change', filterSubscriptions);
                    filterSubscriptions();
                }

                if (toggleAdvancedFilters && advancedFiltersPanel) {
                    toggleAdvancedFilters.addEventListener('click', function () {
                        advancedFiltersPanel.classList.toggle('hidden');
                    });
                }

                if (sortButtons.length > 0) {
                    sortButtons.forEach((button) => {
                        button.addEventListener('click', function () {
                            const key = button.dataset.sortKey;
                            const type = button.dataset.sortType || 'text';
                            const nextDirection = currentSort.key === key && currentSort.direction === 'asc' ? 'desc' : 'asc';

                            currentSort = { key, direction: nextDirection };
                            sortTicketRows(key, type, nextDirection);
                            updateSortIcons();
                        });
                    });
                }

                if (bulkSelectAll && bulkCheckboxes.length > 0) {
                    bulkSelectAll.addEventListener('change', function () {
                        bulkCheckboxes.forEach((checkbox) => {
                            checkbox.checked = bulkSelectAll.checked;
                        });

                        updateBulkSelectionCount();
                    });

                    bulkCheckboxes.forEach((checkbox) => {
                        checkbox.addEventListener('change', function () {
                            bulkSelectAll.checked = bulkCheckboxes.every((item) => item.checked);
                            updateBulkSelectionCount();
                        });
                    });
                }

                updateBulkSelectionCount();
                updateSortIcons();

                @if ($errors->any())
                    openModal('createTicketDrawer');
                @endif

                if (window.lucide) {
                    window.lucide.createIcons();
                }
            });
        </script>
    @endpush
</x-app-layout>
