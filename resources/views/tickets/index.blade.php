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
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6 md:p-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white">Ticket Support</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
                        Dashboard operasional untuk triage tiket, update status, dan tindak lanjut support client.
                    </p>
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

            <div class="mt-8 grid grid-cols-2 xl:grid-cols-5 gap-4">
                <div class="rounded-[1.5rem] border border-blue-100 dark:border-blue-900/30 bg-blue-50/80 dark:bg-blue-900/10 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-blue-600 dark:text-blue-300">Open</p>
                    <p class="mt-3 text-3xl font-black text-slate-900 dark:text-white">{{ $summaryCounts['open'] }}</p>
                </div>
                <div class="rounded-[1.5rem] border border-yellow-100 dark:border-yellow-900/30 bg-yellow-50/80 dark:bg-yellow-900/10 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-yellow-700 dark:text-yellow-300">In Progress</p>
                    <p class="mt-3 text-3xl font-black text-slate-900 dark:text-white">{{ $summaryCounts['in_progress'] }}</p>
                </div>
                <div class="rounded-[1.5rem] border border-purple-100 dark:border-purple-900/30 bg-purple-50/80 dark:bg-purple-900/10 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-purple-700 dark:text-purple-300">Waiting Client</p>
                    <p class="mt-3 text-3xl font-black text-slate-900 dark:text-white">{{ $summaryCounts['waiting_client'] }}</p>
                </div>
                <div class="rounded-[1.5rem] border border-red-100 dark:border-red-900/30 bg-red-50/80 dark:bg-red-900/10 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-red-700 dark:text-red-300">Urgent</p>
                    <p class="mt-3 text-3xl font-black text-slate-900 dark:text-white">{{ $summaryCounts['urgent'] }}</p>
                </div>
                <div class="rounded-[1.5rem] border border-amber-100 dark:border-amber-900/30 bg-amber-50/80 dark:bg-amber-900/10 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-amber-700 dark:text-amber-300">Unassigned</p>
                    <p class="mt-3 text-3xl font-black text-slate-900 dark:text-white">{{ $summaryCounts['unassigned'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-4 md:p-6">
            <div class="flex flex-wrap gap-3">
                @foreach($ticketViews as $value => $item)
                    <a href="{{ route('tickets.index', array_merge(request()->except('page', 'view'), ['view' => $value])) }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border text-sm font-bold transition-colors {{ $view === $value ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/30 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                        <span>{{ $item['label'] }}</span>
                        <span class="inline-flex items-center justify-center min-w-[1.75rem] h-7 px-2 rounded-full text-xs {{ $view === $value ? 'bg-white/20 text-white' : 'bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-300' }}">
                            {{ $item['count'] }}
                        </span>
                    </a>
                @endforeach
            </div>

            <form method="GET" class="mt-5 space-y-4">
                <input type="hidden" name="view" value="{{ $view }}">

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                    <div class="lg:col-span-6">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Pencarian</label>
                        <input type="text" name="q" value="{{ request('q') }}"
                            placeholder="Cari nomor tiket, subjek, isi ticket, nama client, atau client code"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Status</label>
                        <select name="status"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua</option>
                            @foreach(['open', 'in_progress', 'waiting_client', 'resolved', 'closed'] as $status)
                                <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Queue</label>
                        <select name="queue"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua</option>
                            @foreach($ticketQueues as $value => $label)
                                <option value="{{ $value }}" {{ request('queue') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Priority</label>
                        <select name="priority"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua</option>
                            @foreach(['low', 'normal', 'high', 'urgent'] as $priority)
                                <option value="{{ $priority }}" {{ request('priority') === $priority ? 'selected' : '' }}>{{ ucfirst($priority) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-3">
                        <button type="button"
                            id="toggleAdvancedFilters"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                            <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                            More Filters
                        </button>
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                            <i data-lucide="search" class="w-4 h-4"></i>
                            Terapkan
                        </button>
                        <a href="{{ route('tickets.index') }}"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                            Reset
                        </a>
                    </div>
                    <div class="text-sm font-medium text-slate-500 dark:text-slate-400">
                        {{ $tickets->count() }} tiket tampil
                    </div>
                </div>

                <div id="advancedFiltersPanel"
                    class="{{ $advancedFiltersOpen ? '' : 'hidden' }} rounded-[1.5rem] border border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-900/30 p-4 md:p-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
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
                                    <th class="p-4">Tiket</th>
                                    <th class="p-4">Issue</th>
                                    <th class="p-4">Queue / Status</th>
                                    <th class="p-4">Assignee</th>
                                    <th class="p-4">Aktivitas</th>
                                    <th class="p-4 pr-6 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                @foreach($tickets as $ticket)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors {{ $ticket->priority === 'urgent' ? 'bg-red-50/40 dark:bg-red-900/5' : '' }}">
                                        <td class="p-4 pl-6 align-top">
                                            <input type="checkbox" name="ticket_ids[]" value="{{ $ticket->id }}"
                                                class="bulk-ticket-checkbox rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                        </td>
                                        <td class="p-4 align-top min-w-[210px]">
                                            <div class="font-mono font-bold text-slate-800 dark:text-white">{{ $ticket->ticket_number }}</div>
                                            <div class="mt-1 text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $ticket->client->name }}</div>
                                            <div class="text-xs text-slate-500">{{ $ticket->client->client_code }}</div>
                                            <div class="mt-2 text-xs text-slate-500">{{ $ticket->created_at?->format('d M Y H:i') }}</div>
                                        </td>
                                        <td class="p-4 align-top min-w-[300px]">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <div class="font-bold text-slate-800 dark:text-white">{{ $ticket->subject }}</div>
                                                @if(($ticket->unread_staff_replies_count ?? 0) > 0)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300">
                                                        {{ $ticket->unread_staff_replies_count }} balasan baru
                                                    </span>
                                                @endif
                                                @if(is_null($ticket->assigned_to))
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                                                        Unassigned
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="mt-2 text-sm text-slate-500 dark:text-slate-400 max-w-[420px] overflow-hidden">
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
                                        <td class="p-4 align-top min-w-[190px]">
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
                                            </div>
                                        </td>
                                        <td class="p-4 align-top min-w-[170px]">
                                            <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                                                {{ $ticket->assignedUser?->name ?? 'Belum di-assign' }}
                                            </div>
                                            <div class="mt-2 text-xs text-slate-500">
                                                {{ $ticket->client->primaryContact?->name ?? '-' }}
                                            </div>
                                            <div class="text-xs text-slate-500">
                                                {{ $ticket->client->primaryContact?->phone ?? '-' }}
                                            </div>
                                        </td>
                                        <td class="p-4 align-top min-w-[150px]">
                                            <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                                                {{ $ticket->replies_count ?? 0 }} balasan
                                            </div>
                                            <div class="mt-2 text-xs text-slate-500">
                                                Updated {{ $ticket->updated_at?->diffForHumans() }}
                                            </div>
                                        </td>
                                        <td class="p-4 pr-6 align-top">
                                            <div class="flex flex-col items-end gap-2">
                                                <a href="{{ route('tickets.show', $ticket) }}"
                                                    class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-bold text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors">
                                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                                    Detail
                                                </a>
                                                <button type="button"
                                                    data-modal-target="quickEditTicketModal-{{ $ticket->id }}"
                                                    class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                                                    <i data-lucide="square-pen" class="w-4 h-4"></i>
                                                    Quick Edit
                                                </button>
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
