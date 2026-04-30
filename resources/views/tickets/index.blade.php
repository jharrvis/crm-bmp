<x-app-layout>
    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6 md:p-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <div>
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white">Daftar Tiket Support</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Pantau tiket dari client portal dan tindak lanjuti thread support.</p>
                </div>
            </div>

            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Filter Status</label>
                    <select name="status"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Status</option>
                        @foreach(['open', 'in_progress', 'waiting_client', 'resolved', 'closed'] as $status)
                            <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Filter Kategori</label>
                    <select name="category"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Kategori</option>
                        @foreach(['connectivity', 'billing', 'technical', 'general'] as $category)
                            <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>{{ ucfirst($category) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-3">
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl font-bold bg-blue-600 text-white hover:bg-blue-700 transition-colors">Terapkan</button>
                    <a href="{{ route('tickets.index') }}"
                        class="px-5 py-2.5 rounded-xl font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">Reset</a>
                </div>
            </form>

            <div class="overflow-x-auto no-scrollbar">
                <table id="ticketsTable" class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-700">
                            <th class="p-4 pl-6">No. Tiket</th>
                            <th class="p-4">Client</th>
                            <th class="p-4">Subjek</th>
                            <th class="p-4">Kategori</th>
                            <th class="p-4">Status</th>
                            <th class="p-4">Assigned</th>
                            <th class="p-4 pr-6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse($tickets as $ticket)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="p-4 pl-6">
                                    <div class="font-mono font-bold text-slate-700 dark:text-slate-200">{{ $ticket->ticket_number }}</div>
                                    <div class="text-xs text-slate-500">{{ $ticket->created_at?->format('d M Y H:i') }}</div>
                                </td>
                                <td class="p-4">
                                    <div class="font-bold text-slate-800 dark:text-white">{{ $ticket->client->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $ticket->client->client_code }}</div>
                                </td>
                                <td class="p-4">
                                    <div class="font-bold text-slate-800 dark:text-white">{{ $ticket->subject }}</div>
                                    <div class="text-xs text-slate-500 truncate max-w-[260px]">{{ $ticket->message }}</div>
                                </td>
                                <td class="p-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                        {{ ucfirst($ticket->category) }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    @php
                                        $statusClasses = [
                                            'open' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                            'in_progress' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                            'waiting_client' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                                            'resolved' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                            'closed' => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $statusClasses[$ticket->status] ?? 'bg-slate-100 text-slate-700' }}">
                                        {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                    </span>
                                </td>
                                <td class="p-4 text-sm text-slate-600 dark:text-slate-300">
                                    {{ $ticket->assignedUser?->name ?? '-' }}
                                </td>
                                <td class="p-4 pr-6 text-center">
                                    <a href="{{ route('tickets.show', $ticket) }}"
                                        class="inline-flex items-center gap-1 px-3 py-2 rounded-lg text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 font-bold text-sm transition-colors">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-10 text-center text-slate-500 dark:text-slate-400">Belum ada tiket support.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <script>
            $(document).ready(function () {
                $('#ticketsTable').DataTable({
                    language: {
                        search: "",
                        searchPlaceholder: "Cari tiket...",
                        lengthMenu: "Tampilkan _MENU_",
                        info: "_START_ - _END_ dari _TOTAL_",
                        paginate: { first: "«", last: "»", next: "›", previous: "‹" }
                    },
                    order: [],
                    drawCallback: function () { lucide.createIcons(); }
                });
            });
        </script>
    @endpush
</x-app-layout>
