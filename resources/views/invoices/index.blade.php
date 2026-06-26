<x-app-layout>
    @php
        $statusClasses = [
            'unpaid' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
            'paid' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
            'overdue' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
            'cancelled' => 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400',
        ];

        $labels = [
            'unpaid' => 'Belum Lunas',
            'paid' => 'Lunas',
            'overdue' => 'Terlambat',
            'cancelled' => 'Batal',
        ];

        $advancedFiltersOpen = request()->filled('status')
            || request()->filled('date_from')
            || request()->filled('date_to')
            || request()->filled('due_from')
            || request()->filled('due_to');

        $invoiceViews = [
            'all' => ['label' => 'Semua', 'count' => $summaryCounts['total']],
            'unpaid' => ['label' => 'Belum Lunas', 'count' => $summaryCounts['unpaid']],
            'paid' => ['label' => 'Lunas', 'count' => $summaryCounts['paid']],
            'overdue' => ['label' => 'Terlambat', 'count' => $summaryCounts['overdue']],
            'cancelled' => ['label' => 'Batal', 'count' => $summaryCounts['cancelled']],
        ];
    @endphp

    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-6 py-6 md:px-8 border-b border-slate-200 dark:border-slate-700">
                <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5">
                    <div>
                        <h3 class="text-2xl font-bold text-slate-800 dark:text-white">Daftar Tagihan</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
                            Pantau invoice, jatuh tempo, dan status pembayaran dalam satu tampilan yang lebih ringkas.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        @can('invoices.create')
                            <a href="{{ route('invoices.create') }}"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                                <i data-lucide="file-plus-2" class="w-4 h-4"></i>
                                <span>Buat Invoice Manual</span>
                            </a>
                        @endcan
                        <button onclick="generateInvoices()" id="btnGenerate"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>Generate Bulanan</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="px-6 py-6 md:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    <div class="rounded-[1.5rem] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                        <p class="text-sm font-semibold text-slate-400">Overdue</p>
                        <p class="mt-3 text-3xl font-black text-slate-900 dark:text-white">
                            Rp {{ number_format($overviewMetrics['overdue_amount'], 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="rounded-[1.5rem] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                        <p class="text-sm font-semibold text-slate-400">Jatuh Tempo 30 Hari</p>
                        <p class="mt-3 text-3xl font-black text-slate-900 dark:text-white">
                            Rp {{ number_format($overviewMetrics['due_soon_amount'], 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="rounded-[1.5rem] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                        <p class="text-sm font-semibold text-slate-400">Rata-rata Pelunasan</p>
                        <p class="mt-3 text-3xl font-black text-slate-900 dark:text-white">
                            {{ $overviewMetrics['average_paid_days'] }} hari
                        </p>
                    </div>
                    <div class="rounded-[1.5rem] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                        <p class="text-sm font-semibold text-slate-400">Lunas Bulan Ini</p>
                        <p class="mt-3 text-3xl font-black text-slate-900 dark:text-white">
                            Rp {{ number_format($overviewMetrics['paid_this_month_amount'], 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-6 py-6 md:px-8 border-b border-slate-200 dark:border-slate-700">
                <div>
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white">Invoices</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
                        Daftar invoice terbaru dengan filter dan aksi yang lebih fokus.
                    </p>
                </div>
            </div>

            <form method="GET" id="invoiceFilterForm" class="px-6 py-5 md:px-8 space-y-4">
                <input type="hidden" name="view" value="{{ $view }}">

                <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                    <div class="flex w-full xl:flex-1 xl:min-w-0 xl:flex-wrap items-center rounded-2xl bg-slate-100 dark:bg-slate-700/60 p-1.5 overflow-x-auto no-scrollbar">
                        @foreach ($invoiceViews as $value => $item)
                            <button type="button" data-view-button data-view="{{ $value }}"
                                class="inline-flex shrink-0 items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold transition-colors {{ $view === $value ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white' }}">
                                <span>{{ $item['label'] }}</span>
                                <span class="text-xs {{ $view === $value ? 'text-slate-400 dark:text-slate-400' : 'text-slate-400 dark:text-slate-500' }}">{{ $item['count'] }}</span>
                            </button>
                        @endforeach
                    </div>
                    <div class="flex flex-col md:flex-row gap-3 xl:w-[460px] xl:min-w-[460px] xl:shrink-0">
                        <div class="relative flex-1">
                            <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                            <input type="text" name="q" value="{{ request('q') }}"
                                placeholder="Cari invoice, nama pelanggan, atau client code"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 pl-11 pr-4 py-3 bg-white dark:bg-slate-700/30 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <button type="button"
                            id="toggleAdvancedFilters"
                            class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700/30 text-slate-700 dark:text-slate-200 font-bold hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                            <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                            Filter
                        </button>
                    </div>
                </div>

                <div id="advancedFiltersPanel"
                    class="{{ $advancedFiltersOpen ? '' : 'hidden' }} rounded-[1.5rem] border border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-900/30 p-4 md:p-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Status</label>
                            <select name="status"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Semua</option>
                                @foreach (['unpaid', 'paid', 'overdue', 'cancelled'] as $status)
                                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                                        {{ $labels[$status] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Tanggal Invoice Dari</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Tanggal Invoice Sampai</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Jatuh Tempo Dari</label>
                            <input type="date" name="due_from" value="{{ request('due_from') }}"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Jatuh Tempo Sampai</label>
                            <input type="date" name="due_to" value="{{ request('due_to') }}"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="mt-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-t border-slate-200 dark:border-slate-700 pt-4">
                        <p id="invoiceVisibleCount" class="text-sm text-slate-500 dark:text-slate-400">{{ $invoices->count() }} invoice tampil</p>
                        <button type="button" id="resetInvoiceFilters"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                            Reset Filter
                        </button>
                    </div>
                </div>
            </form>

            <div class="border-t border-slate-200 dark:border-slate-700">
                <div id="invoiceEmptyState" class="{{ $invoices->isEmpty() ? '' : 'hidden' }} p-10 text-center text-slate-500 dark:text-slate-400">
                    Belum ada invoice pada filter ini.
                </div>
                <div id="invoiceTableWrapper" class="{{ $invoices->isEmpty() ? 'hidden' : '' }} overflow-x-auto no-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-700">
                                <th class="p-4 pl-6">
                                    <button type="button" data-sort-button data-sort-key="invoice" data-sort-type="text"
                                        class="inline-flex items-center gap-2 text-left text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                                        <span>Invoice Number</span>
                                        <i data-lucide="arrow-up-down" class="w-3.5 h-3.5 sort-icon"></i>
                                    </button>
                                </th>
                                <th class="p-4">
                                    <button type="button" data-sort-button data-sort-key="customer" data-sort-type="text"
                                        class="inline-flex items-center gap-2 text-left text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                                        <span>Customer</span>
                                        <i data-lucide="arrow-up-down" class="w-3.5 h-3.5 sort-icon"></i>
                                    </button>
                                </th>
                                <th class="p-4">
                                    <button type="button" data-sort-button data-sort-key="dueDate" data-sort-type="date"
                                        class="inline-flex items-center gap-2 text-left text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                                        <span>Tanggal</span>
                                        <i data-lucide="arrow-up-down" class="w-3.5 h-3.5 sort-icon"></i>
                                    </button>
                                </th>
                                <th class="p-4">
                                    <button type="button" data-sort-button data-sort-key="total" data-sort-type="number"
                                        class="inline-flex items-center gap-2 text-left text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                                        <span>Total</span>
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
                        <tbody id="invoiceTableBody" class="divide-y divide-slate-100 dark:divide-slate-700">
                            @foreach ($invoices as $invoice)
                                <tr
                                    data-row-type="invoice"
                                    data-view-status="{{ $invoice->status }}"
                                    data-status="{{ $invoice->status }}"
                                    data-search="{{ strtolower($invoice->invoice_number . ' ' . $invoice->client->name . ' ' . $invoice->client->client_code) }}"
                                    data-invoice="{{ strtolower($invoice->invoice_number) }}"
                                    data-customer="{{ strtolower($invoice->client->name) }}"
                                    data-invoice-date="{{ $invoice->invoice_date?->timestamp ?? 0 }}"
                                    data-due-date="{{ $invoice->due_date?->timestamp ?? 0 }}"
                                    data-total="{{ (float) $invoice->total_amount }}"
                                >
                                    <td class="p-4 pl-6 align-top">
                                        <div class="font-bold text-slate-800 dark:text-white">{{ $invoice->invoice_number }}</div>
                                        <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                            Inv {{ optional($invoice->invoice_date)->translatedFormat('d M Y') }}
                                        </div>
                                    </td>
                                    <td class="p-4 align-top">
                                        <div class="font-semibold text-slate-800 dark:text-white">{{ $invoice->client->name }}</div>
                                        <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $invoice->client->client_code }}</div>
                                    </td>
                                    <td class="p-4 align-top">
                                        <div class="font-semibold text-slate-800 dark:text-white">{{ optional($invoice->invoice_date)->translatedFormat('d M Y') }}</div>
                                        <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">Due {{ optional($invoice->due_date)->translatedFormat('d M Y') }}</div>
                                    </td>
                                    <td class="p-4 align-top font-bold text-slate-800 dark:text-white">
                                        Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="p-4 align-top">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold {{ $statusClasses[$invoice->status] ?? 'bg-slate-100 text-slate-600' }}">
                                            {{ $labels[$invoice->status] ?? ucfirst($invoice->status) }}
                                        </span>
                                        @if($invoice->paid_at)
                                            <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                                Lunas {{ $invoice->paid_at->translatedFormat('d M Y') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="p-4 pr-6 align-top">
                                        <div class="flex justify-end">
                                            <details class="relative">
                                                <summary class="list-none inline-flex h-10 w-10 cursor-pointer items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-700">
                                                    <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                                                </summary>
                                                <div class="absolute right-0 z-20 mt-2 w-48 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl dark:border-slate-700 dark:bg-slate-800">
                                                    <a href="{{ route('invoices.show', $invoice) }}"
                                                        class="w-full flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                                        <i data-lucide="file-text" class="w-4 h-4"></i>
                                                        Lihat Detail
                                                    </a>

                                                    @can('invoices.update')
                                                        @if($invoice->status === 'unpaid')
                                                            <button type="button" onclick="markAsPaid({{ $invoice->id }}, '{{ $invoice->invoice_number }}')"
                                                                class="w-full flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                                                Tandai Lunas
                                                            </button>
                                                        @endif
                                                    @endcan

                                                    @can('invoices.delete')
                                                        <button type="button" onclick="deleteInvoice({{ $invoice->id }})"
                                                            class="w-full flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                            Delete
                                                        </button>
                                                    @endcan
                                                </div>
                                            </details>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const invoiceFilterForm = document.getElementById('invoiceFilterForm');
                const filterSearchInput = invoiceFilterForm?.querySelector('input[name="q"]');
                const autoSubmitFields = invoiceFilterForm
                    ? Array.from(invoiceFilterForm.querySelectorAll('select[name], input[type="date"][name]'))
                    : [];
                const advancedFiltersPanel = document.getElementById('advancedFiltersPanel');
                const toggleAdvancedFilters = document.getElementById('toggleAdvancedFilters');
                const invoiceTableBody = document.getElementById('invoiceTableBody');
                const invoiceTableWrapper = document.getElementById('invoiceTableWrapper');
                const invoiceEmptyState = document.getElementById('invoiceEmptyState');
                const invoiceVisibleCount = document.getElementById('invoiceVisibleCount');
                const resetInvoiceFilters = document.getElementById('resetInvoiceFilters');
                const viewButtons = Array.from(document.querySelectorAll('[data-view-button]'));
                const sortButtons = Array.from(document.querySelectorAll('[data-sort-button]'));
                let filterDebounceTimer = null;
                let currentView = new URLSearchParams(window.location.search).get('view') || '{{ $view }}';
                let currentSort = { key: null, direction: 'asc' };
                const defaultFilterState = {
                    q: '',
                    status: '',
                    date_from: '',
                    date_to: '',
                    due_from: '',
                    due_to: '',
                };

                function syncInvoiceUrl() {
                    const params = new URLSearchParams();
                    const formData = new FormData(invoiceFilterForm);

                    if (currentView && currentView !== 'all') {
                        params.set('view', currentView);
                    }

                    Object.keys(defaultFilterState).forEach((key) => {
                        const value = (formData.get(key) || '').toString().trim();
                        if (value) {
                            params.set(key, value);
                        }
                    });

                    const query = params.toString();
                    const nextUrl = query ? `${window.location.pathname}?${query}` : window.location.pathname;
                    window.history.replaceState({}, '', nextUrl);
                }

                function updateInvoiceViewButtons() {
                    viewButtons.forEach((button) => {
                        const isActive = button.dataset.view === currentView;
                        button.className = `inline-flex shrink-0 items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold transition-colors ${
                            isActive
                                ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm'
                                : 'text-slate-500 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white'
                        }`;

                        const count = button.querySelector('span:last-child');
                        if (count) {
                            count.className = `text-xs ${isActive ? 'text-slate-400 dark:text-slate-400' : 'text-slate-400 dark:text-slate-500'}`;
                        }
                    });
                }

                function updateSortIcons() {
                    sortButtons.forEach((button) => {
                        const icon = button.querySelector('.sort-icon');
                        if (!icon) return;

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

                function sortInvoiceRows(key, type, direction) {
                    if (!invoiceTableBody) return;

                    const rows = Array.from(invoiceTableBody.querySelectorAll('tr[data-row-type="invoice"]'));
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

                    rows.forEach((row) => invoiceTableBody.appendChild(row));
                }

                function applyInvoiceFilters() {
                    if (!invoiceTableBody || !invoiceFilterForm) return;

                    const formData = new FormData(invoiceFilterForm);
                    const query = (formData.get('q') || '').toString().trim().toLowerCase();
                    const status = (formData.get('status') || '').toString();
                    const dateFrom = (formData.get('date_from') || '').toString();
                    const dateTo = (formData.get('date_to') || '').toString();
                    const dueFrom = (formData.get('due_from') || '').toString();
                    const dueTo = (formData.get('due_to') || '').toString();

                    const invoiceFromTs = dateFrom ? new Date(`${dateFrom}T00:00:00`).getTime() / 1000 : null;
                    const invoiceToTs = dateTo ? new Date(`${dateTo}T23:59:59`).getTime() / 1000 : null;
                    const dueFromTs = dueFrom ? new Date(`${dueFrom}T00:00:00`).getTime() / 1000 : null;
                    const dueToTs = dueTo ? new Date(`${dueTo}T23:59:59`).getTime() / 1000 : null;

                    let visibleCount = 0;
                    const rows = Array.from(invoiceTableBody.querySelectorAll('tr[data-row-type="invoice"]'));

                    rows.forEach((row) => {
                        const matchesView = currentView === 'all' || row.dataset.viewStatus === currentView;
                        const matchesQuery = !query || (row.dataset.search || '').includes(query);
                        const matchesStatus = !status || row.dataset.status === status;
                        const invoiceDate = Number(row.dataset.invoiceDate || 0);
                        const dueDate = Number(row.dataset.dueDate || 0);
                        const matchesInvoiceFrom = invoiceFromTs === null || invoiceDate >= invoiceFromTs;
                        const matchesInvoiceTo = invoiceToTs === null || invoiceDate <= invoiceToTs;
                        const matchesDueFrom = dueFromTs === null || dueDate >= dueFromTs;
                        const matchesDueTo = dueToTs === null || dueDate <= dueToTs;

                        const isVisible = matchesView
                            && matchesQuery
                            && matchesStatus
                            && matchesInvoiceFrom
                            && matchesInvoiceTo
                            && matchesDueFrom
                            && matchesDueTo;

                        row.classList.toggle('hidden', !isVisible);
                        if (isVisible) visibleCount++;
                    });

                    if (invoiceVisibleCount) {
                        invoiceVisibleCount.textContent = `${visibleCount} invoice tampil`;
                    }

                    invoiceTableWrapper?.classList.toggle('hidden', visibleCount === 0);
                    invoiceEmptyState?.classList.toggle('hidden', visibleCount !== 0);

                    syncInvoiceUrl();
                    updateInvoiceViewButtons();
                }

                toggleAdvancedFilters?.addEventListener('click', function () {
                    advancedFiltersPanel.classList.toggle('hidden');
                });

                if (invoiceFilterForm && filterSearchInput) {
                    filterSearchInput.addEventListener('input', function () {
                        clearTimeout(filterDebounceTimer);
                        filterDebounceTimer = setTimeout(applyInvoiceFilters, 400);
                    });
                }

                autoSubmitFields.forEach((field) => {
                    field.addEventListener('change', applyInvoiceFilters);
                });

                viewButtons.forEach((button) => {
                    button.addEventListener('click', function () {
                        currentView = button.dataset.view || 'all';
                        applyInvoiceFilters();
                    });
                });

                resetInvoiceFilters?.addEventListener('click', function () {
                    invoiceFilterForm.reset();
                    currentView = 'all';
                    applyInvoiceFilters();
                });

                sortButtons.forEach((button) => {
                    button.addEventListener('click', function () {
                        const key = button.dataset.sortKey;
                        const type = button.dataset.sortType || 'text';
                        const nextDirection = currentSort.key === key && currentSort.direction === 'asc' ? 'desc' : 'asc';

                        currentSort = { key, direction: nextDirection };
                        sortInvoiceRows(key, type, nextDirection);
                        updateSortIcons();
                    });
                });

                updateSortIcons();
                updateInvoiceViewButtons();
                applyInvoiceFilters();

                if (window.lucide) {
                    window.lucide.createIcons();
                }
            });

            async function generateInvoices() {
                const btn = document.getElementById('btnGenerate');
                const confirmed = await window.confirmAction('Generate Invoice?', 'Generate invoice otomatis untuk pelanggan aktif bulan ini?');
                if (!confirmed) return;

                btn.disabled = true;
                btn.innerHTML = '<i class="animate-spin" data-lucide="loader-2"></i> Generating...';
                lucide.createIcons();

                fetch("{{ route('invoices.generate') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    }
                })
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            window.location.reload();
                        } else {
                            alert('Gagal: ' + res.message);
                            btn.disabled = false;
                            btn.innerHTML = '<i data-lucide="plus"></i> Generate Bulanan';
                            lucide.createIcons();
                        }
                    })
                    .catch(err => {
                        alert('Terjadi kesalahan server.');
                        console.error(err);
                        btn.disabled = false;
                    });
            }

            async function markAsPaid(id, number) {
                const confirmed = await window.confirmAction('Tandai Lunas?', `Tandai invoice ${number} sebagai LUNAS?`);
                if (!confirmed) return;

                fetch(`/invoices/${id}`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: 'paid' })
                })
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            window.location.reload();
                        } else {
                            alert('Gagal update status.');
                        }
                    });
            }

            async function deleteInvoice(id) {
                const confirmed = await window.confirmAction('Hapus Invoice?', 'Hapus invoice ini? Data tidak bisa dikembalikan.');
                if (!confirmed) return;

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/invoices/${id}`;
                form.classList.add('hidden');

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = "{{ csrf_token() }}";

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';

                form.appendChild(csrfInput);
                form.appendChild(methodInput);
                document.body.appendChild(form);
                form.submit();
            }
        </script>
    @endpush
</x-app-layout>
