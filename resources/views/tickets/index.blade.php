<x-app-layout>
    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6 md:p-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <div>
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white">Daftar Tiket Support</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Pantau tiket dari client portal dan tindak lanjuti thread support.</p>
                </div>
            </div>

            <div class="rounded-[1.75rem] border border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-900/30 p-5 md:p-6 mb-8">
                <div class="mb-5">
                    <h4 class="text-sm font-bold uppercase tracking-widest text-slate-500">Buat Ticket Baru</h4>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Gunakan form ini jika client melapor lewat telepon, WhatsApp, atau belum bisa membuat tiket dari portal sendiri.</p>
                </div>

                <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data" class="space-y-5">
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

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
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
                        <textarea name="message" rows="4" required
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

                    <div class="flex justify-end">
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl font-bold bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                            Buat Ticket
                        </button>
                    </div>
                </form>
            </div>

            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
                <div class="md:col-span-2 xl:col-span-4">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Pencarian</label>
                    <input type="text" name="q" value="{{ request('q') }}"
                        placeholder="Cari nomor tiket, subjek, isi ticket, nama client, atau client code"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                </div>
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
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Filter Priority</label>
                    <select name="priority"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Priority</option>
                        @foreach(['low', 'normal', 'high', 'urgent'] as $priority)
                            <option value="{{ $priority }}" {{ request('priority') === $priority ? 'selected' : '' }}>{{ ucfirst($priority) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Filter Client</label>
                    <select name="client_id"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Client</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ (string) request('client_id') === (string) $client->id ? 'selected' : '' }}>
                                {{ $client->name }} ({{ $client->client_code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Filter Assignee</label>
                    <select name="assigned_to"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
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
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex items-end gap-3 md:col-span-2 xl:col-span-4">
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl font-bold bg-blue-600 text-white hover:bg-blue-700 transition-colors">Terapkan</button>
                    <a href="{{ route('tickets.index') }}"
                        class="px-5 py-2.5 rounded-xl font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">Reset</a>
                </div>
            </form>

            @if($tickets->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-200 dark:border-slate-700 p-10 text-center text-slate-500 dark:text-slate-400">
                    Belum ada tiket support.
                </div>
            @else
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
                            @foreach($tickets as $ticket)
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
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <div class="font-bold text-slate-800 dark:text-white">{{ $ticket->subject }}</div>
                                        @if(($ticket->unread_staff_replies_count ?? 0) > 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300">
                                                {{ $ticket->unread_staff_replies_count }} balasan baru
                                            </span>
                                        @endif
                                    </div>
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
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <script>
            $(document).ready(function () {
                const clientSelect = document.getElementById('client_id');
                const subscriptionSelect = document.getElementById('subscription_id');

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

                if (clientSelect && subscriptionSelect) {
                    clientSelect.addEventListener('change', filterSubscriptions);
                    filterSubscriptions();
                }

                if (!document.getElementById('ticketsTable')) {
                    return;
                }

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
