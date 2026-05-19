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

        $manualInvoiceClientOptions = $clients->mapWithKeys(function ($client) {
            return [
                $client->id => $client->subscriptions->map(function ($subscription) {
                    $serviceName = $subscription->package?->service?->name;
                    $packageName = $subscription->package?->name ?? 'Layanan';
                    $taxLabels = collect([
                        $subscription->uses_ppn ? 'PPN' : null,
                        $subscription->uses_pph23 ? 'PPh23' : null,
                    ])->filter()->implode(' + ');

                    return [
                        'id' => $subscription->id,
                        'label' => trim(collect([
                            $subscription->subscription_code,
                            $serviceName ? $serviceName . ' - ' . $packageName : $packageName,
                            'Rp ' . number_format($subscription->base_price, 0, ',', '.'),
                            $taxLabels ? '(' . $taxLabels . ')' : null,
                        ])->filter()->implode(' • ')),
                        'description' => trim(collect([
                            'Langganan',
                            $serviceName ? $serviceName . ' - ' . $packageName : $packageName,
                            $subscription->subscription_code ? '[' . $subscription->subscription_code . ']' : null,
                        ])->filter()->implode(' ')),
                        'amount' => (float) $subscription->base_price,
                    ];
                })->values()->all(),
            ];
        })->all();

        $manualInvoicePackageOptions = $packages->map(function ($package) {
            $serviceName = $package->service?->name;

            return [
                'id' => $package->id,
                'label' => trim(collect([
                    $serviceName ? $serviceName . ' - ' . $package->name : $package->name,
                    'Rp ' . number_format($package->price, 0, ',', '.'),
                ])->filter()->implode(' • ')),
                'description' => trim(($serviceName ? $serviceName . ' - ' : '') . $package->name),
                'amount' => (float) $package->price,
            ];
        })->values()->all();
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
                            <button type="button" data-modal-target="createInvoiceDrawer"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                                <i data-lucide="file-plus-2" class="w-4 h-4"></i>
                                <span>Buat Invoice Manual</span>
                            </button>
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
                <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5">
                    <div>
                        <h3 class="text-2xl font-bold text-slate-800 dark:text-white">Invoices</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
                            Daftar invoice terbaru dengan filter dan aksi yang lebih fokus.
                        </p>
                    </div>
                </div>
            </div>

            <form method="GET" id="invoiceFilterForm" class="px-6 py-5 md:px-8 space-y-4">
                <input type="hidden" name="view" value="{{ $view }}">

                <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                    <div class="flex w-full xl:flex-1 xl:min-w-0 xl:flex-wrap items-center rounded-2xl bg-slate-100 dark:bg-slate-700/60 p-1.5 overflow-x-auto no-scrollbar">
                        @foreach($invoiceViews as $value => $item)
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
                                @foreach(['unpaid', 'paid', 'overdue', 'cancelled'] as $status)
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
                                @foreach($invoices as $invoice)
                                    <tr
                                        data-row-type="invoice"
                                        data-view-status="{{ $invoice->status }}"
                                        data-search="{{ strtolower($invoice->invoice_number . ' ' . $invoice->client->name . ' ' . $invoice->client->client_code) }}"
                                        data-invoice="{{ strtolower($invoice->invoice_number) }}"
                                        data-customer="{{ strtolower($invoice->client->name) }}"
                                        data-invoice-date="{{ $invoice->invoice_date?->timestamp ?? 0 }}"
                                        data-due-date="{{ $invoice->due_date?->timestamp ?? 0 }}"
                                        data-total="{{ (float) $invoice->total_amount }}"
                                        data-status="{{ $invoice->status }}"
                                        class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors {{ $invoice->status === 'overdue' ? 'bg-red-50/40 dark:bg-red-900/5' : '' }}">
                                        <td class="p-4 pl-6 align-top min-w-[220px]">
                                            <div class="font-mono font-bold text-slate-800 dark:text-white">{{ $invoice->invoice_number }}</div>
                                            <div class="mt-1 text-xs text-slate-500">
                                                Inv {{ $invoice->invoice_date?->format('d M Y') }}
                                            </div>
                                        </td>
                                        <td class="p-4 align-top min-w-[240px]">
                                            <div class="text-sm font-semibold text-slate-800 dark:text-white">{{ $invoice->client->name }}</div>
                                            <div class="mt-1 text-xs text-slate-500">{{ $invoice->client->client_code }}</div>
                                        </td>
                                        <td class="p-4 align-top min-w-[210px]">
                                            <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                                                Inv {{ $invoice->invoice_date?->format('d M Y') }}
                                            </div>
                                            <div class="mt-1 text-sm font-semibold {{ $invoice->due_date && $invoice->due_date->isPast() && $invoice->status !== 'paid' ? 'text-red-600 dark:text-red-400' : 'text-slate-700 dark:text-slate-200' }}">
                                                Due {{ $invoice->due_date?->format('d M Y') }}
                                            </div>
                                        </td>
                                        <td class="p-4 align-top min-w-[150px]">
                                            <div class="font-bold text-slate-800 dark:text-white">
                                                Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                                            </div>
                                        </td>
                                        <td class="p-4 align-top min-w-[140px]">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $statusClasses[$invoice->status] ?? '' }}">
                                                {{ $labels[$invoice->status] ?? $invoice->status }}
                                            </span>
                                        </td>
                                        <td class="p-4 pr-6 align-top">
                                            <div class="flex justify-end">
                                                <details class="relative group">
                                                    <summary class="list-none inline-flex items-center justify-center h-10 w-10 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer transition-colors">
                                                        <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                                                    </summary>
                                                    <div class="absolute right-0 top-12 z-20 w-48 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-xl p-2">
                                                        <a href="{{ route('invoices.show', $invoice) }}" target="_blank"
                                                            class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                                            <i data-lucide="printer" class="w-4 h-4"></i>
                                                            View Detail
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

    @can('invoices.create')
        <div id="createInvoiceDrawer" data-modal-root class="fixed inset-0 z-[120] hidden bg-slate-950/60 backdrop-blur-sm">
            <div class="absolute inset-y-0 right-0 w-full max-w-3xl bg-white dark:bg-slate-800 border-l border-slate-200 dark:border-slate-700 shadow-2xl overflow-y-auto">
                <div class="sticky top-0 z-10 flex items-center justify-between gap-4 px-6 py-5 bg-white/95 dark:bg-slate-800/95 backdrop-blur border-b border-slate-200 dark:border-slate-700">
                    <div>
                        <h4 class="text-lg font-bold text-slate-800 dark:text-white">Buat Invoice Manual</h4>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Tambahkan tagihan manual untuk pelanggan tertentu beserta rincian itemnya.</p>
                    </div>
                    <button type="button" data-modal-close class="rounded-xl p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('invoices.store') }}" class="p-6 space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Pelanggan <span class="text-red-500">*</span></label>
                            <select name="client_id" required
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Pilih pelanggan</option>
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
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Tanggal Jatuh Tempo <span class="text-red-500">*</span></label>
                            <input type="date" name="due_date" required value="{{ old('due_date', now()->addDays(7)->toDateString()) }}"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                            @error('due_date')
                                <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300">Rincian Item <span class="text-red-500">*</span></label>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pilih layanan aktif pelanggan atau katalog layanan lain, lalu sesuaikan deskripsi dan nominal bila perlu.</p>
                            </div>
                            <button type="button" id="addInvoiceItem"
                                class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                                <i data-lucide="plus" class="w-4 h-4"></i>
                                Tambah Item
                            </button>
                        </div>

                        <div id="invoiceItemsContainer" class="space-y-3">
                            @php
                                $oldItems = old('items', [[
                                    'source' => 'subscription',
                                    'subscription_id' => '',
                                    'package_id' => '',
                                    'description' => '',
                                    'qty' => 1,
                                    'amount' => '',
                                ]]);
                            @endphp
                            @foreach($oldItems as $index => $item)
                                <div class="invoice-item-row rounded-[1.25rem] border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/30 p-4">
                                    <input type="hidden" name="items[{{ $index }}][subscription_id]" value="{{ $item['subscription_id'] ?? '' }}" class="invoice-item-subscription-id">
                                    <input type="hidden" name="items[{{ $index }}][package_id]" value="{{ $item['package_id'] ?? '' }}" class="invoice-item-package-id">

                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start">
                                        <div class="md:col-span-3">
                                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Sumber Item</label>
                                            <select name="items[{{ $index }}][source]"
                                                class="invoice-item-source w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="subscription" {{ ($item['source'] ?? 'subscription') === 'subscription' ? 'selected' : '' }}>Layanan Pelanggan</option>
                                                <option value="package" {{ ($item['source'] ?? '') === 'package' ? 'selected' : '' }}>Katalog Layanan</option>
                                                <option value="manual" {{ ($item['source'] ?? '') === 'manual' ? 'selected' : '' }}>Item Manual</option>
                                            </select>
                                        </div>
                                        <div class="md:col-span-5">
                                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Pilih Layanan</label>
                                            <select class="invoice-item-option w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="">Pilih item</option>
                                            </select>
                                            <p class="invoice-item-option-hint mt-1 text-[11px] text-slate-500 dark:text-slate-400"></p>
                                        </div>
                                        <div class="md:col-span-3">
                                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Deskripsi</label>
                                            <input type="text" name="items[{{ $index }}][description]" value="{{ $item['description'] ?? '' }}" required
                                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                                                placeholder="Contoh: Biaya instalasi / layanan tambahan">
                                        </div>
                                        <div class="md:col-span-1">
                                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Qty</label>
                                            <input type="number" min="1" name="items[{{ $index }}][qty]" value="{{ $item['qty'] ?? 1 }}" required
                                                class="invoice-item-qty w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div class="md:col-span-3">
                                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nominal</label>
                                            <input type="number" min="0" step="0.01" name="items[{{ $index }}][amount]" value="{{ $item['amount'] ?? '' }}" required
                                                class="invoice-item-amount w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                                                placeholder="0">
                                        </div>
                                        <div class="md:col-span-1 flex md:justify-end">
                                            <button type="button"
                                                class="remove-invoice-item mt-6 inline-flex items-center justify-center h-11 w-11 rounded-xl border border-slate-200 dark:border-slate-700 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('items')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                        @error('items.*.description')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                        @error('items.*.amount')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                        @error('items.*.qty')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Catatan</label>
                        <textarea name="notes" rows="3"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-3 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Catatan tambahan untuk invoice ini">{{ old('notes') }}</textarea>
                    </div>

                    <div class="rounded-[1.25rem] border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/30 p-4 flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Estimasi Total</p>
                            <p id="manualInvoiceTotal" class="mt-2 text-2xl font-black text-slate-900 dark:text-white">Rp 0</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" data-modal-close
                            class="px-5 py-2.5 rounded-xl font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl font-bold bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                            Simpan Invoice
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endcan

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
                const invoiceItemsContainer = document.getElementById('invoiceItemsContainer');
                const addInvoiceItemButton = document.getElementById('addInvoiceItem');
                const manualInvoiceTotal = document.getElementById('manualInvoiceTotal');
                const manualInvoiceClientSelect = document.querySelector('select[name="client_id"]');
                const clientSubscriptionCatalog = @js($manualInvoiceClientOptions);
                const packageCatalog = @js($manualInvoicePackageOptions);
                let filterDebounceTimer = null;
                let currentView = new URLSearchParams(window.location.search).get('view') || '{{ $view }}';
                let currentSort = {
                    key: null,
                    direction: 'asc',
                };
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

                    Object.entries(defaultFilterState).forEach(([key]) => {
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

                function sortInvoiceRows(key, type, direction) {
                    if (!invoiceTableBody) {
                        return;
                    }

                    const rows = Array.from(invoiceTableBody.querySelectorAll('tr'));
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

                function openModal(modalId) {
                    const modal = document.getElementById(modalId);

                    if (!modal) {
                        return;
                    }

                    modal.classList.remove('hidden');

                    if (modal.id === 'createInvoiceDrawer') {
                        document.body.classList.add('overflow-hidden');
                        return;
                    }

                    modal.classList.add('flex');
                }

                function closeModal(modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');

                    if (modal.id === 'createInvoiceDrawer') {
                        document.body.classList.remove('overflow-hidden');
                    }
                }

                function formatCurrency(value) {
                    return new Intl.NumberFormat('id-ID').format(value);
                }

                function updateManualInvoiceTotal() {
                    if (!invoiceItemsContainer || !manualInvoiceTotal) {
                        return;
                    }

                    let total = 0;

                    invoiceItemsContainer.querySelectorAll('.invoice-item-row').forEach((row) => {
                        const qty = Number(row.querySelector('.invoice-item-qty')?.value || 0);
                        const amount = Number(row.querySelector('.invoice-item-amount')?.value || 0);
                        total += qty * amount;
                    });

                    manualInvoiceTotal.textContent = `Rp ${formatCurrency(total)}`;
                }

                function getSubscriptionOptionsForSelectedClient() {
                    const clientId = manualInvoiceClientSelect?.value || '';
                    return clientSubscriptionCatalog[clientId] || [];
                }

                function buildOptionMarkup(options, selectedValue, placeholder) {
                    const optionMarkup = options.map((option) => `
                        <option value="${option.id}" ${String(option.id) === String(selectedValue || '') ? 'selected' : ''}>
                            ${option.label}
                        </option>
                    `).join('');

                    return `<option value="">${placeholder}</option>${optionMarkup}`;
                }

                function syncRowOptionSelector(row) {
                    const sourceField = row.querySelector('.invoice-item-source');
                    const optionField = row.querySelector('.invoice-item-option');
                    const optionHint = row.querySelector('.invoice-item-option-hint');
                    const subscriptionField = row.querySelector('.invoice-item-subscription-id');
                    const packageField = row.querySelector('.invoice-item-package-id');

                    if (!sourceField || !optionField || !subscriptionField || !packageField) {
                        return;
                    }

                    const source = sourceField.value || 'subscription';
                    const subscriptionOptions = getSubscriptionOptionsForSelectedClient();

                    if (source === 'subscription') {
                        optionField.disabled = false;
                        optionField.innerHTML = buildOptionMarkup(subscriptionOptions, subscriptionField.value, subscriptionOptions.length
                            ? 'Pilih layanan pelanggan'
                            : 'Pelanggan ini belum punya layanan aktif');
                        optionHint.textContent = subscriptionOptions.length
                            ? 'Daftar ini mengikuti layanan aktif pelanggan yang dipilih.'
                            : 'Ganti pelanggan atau pilih katalog layanan / item manual.';
                        packageField.value = '';
                        return;
                    }

                    if (source === 'package') {
                        optionField.disabled = false;
                        optionField.innerHTML = buildOptionMarkup(packageCatalog, packageField.value, 'Pilih layanan dari katalog');
                        optionHint.textContent = 'Gunakan untuk layanan tambahan di luar subscription aktif pelanggan.';
                        subscriptionField.value = '';
                        return;
                    }

                    optionField.disabled = true;
                    optionField.innerHTML = '<option value="">Isi item manual</option>';
                    optionHint.textContent = 'Gunakan jika item tidak berasal dari layanan aktif maupun katalog.';
                    subscriptionField.value = '';
                    packageField.value = '';
                }

                function syncRowSelectionToInputs(row, shouldAutofill = true) {
                    const sourceField = row.querySelector('.invoice-item-source');
                    const optionField = row.querySelector('.invoice-item-option');
                    const subscriptionField = row.querySelector('.invoice-item-subscription-id');
                    const packageField = row.querySelector('.invoice-item-package-id');
                    const descriptionField = row.querySelector('input[name*="[description]"]');
                    const amountField = row.querySelector('.invoice-item-amount');

                    if (!sourceField || !optionField || !subscriptionField || !packageField || !descriptionField || !amountField) {
                        return;
                    }

                    const source = sourceField.value || 'subscription';
                    const selectedValue = optionField.value || '';

                    subscriptionField.value = '';
                    packageField.value = '';

                    if (!selectedValue) {
                        return;
                    }

                    if (source === 'subscription') {
                        const subscription = getSubscriptionOptionsForSelectedClient().find((item) => String(item.id) === selectedValue);
                        if (!subscription) {
                            return;
                        }

                        subscriptionField.value = subscription.id;
                        if (shouldAutofill) {
                            descriptionField.value = subscription.description;
                            amountField.value = subscription.amount;
                        }
                        return;
                    }

                    if (source === 'package') {
                        const packageItem = packageCatalog.find((item) => String(item.id) === selectedValue);
                        if (!packageItem) {
                            return;
                        }

                        packageField.value = packageItem.id;
                        if (shouldAutofill) {
                            descriptionField.value = packageItem.description;
                            amountField.value = packageItem.amount;
                        }
                    }
                }

                function bindInvoiceItemRow(row) {
                    row.querySelectorAll('.invoice-item-qty, .invoice-item-amount').forEach((input) => {
                        input.addEventListener('input', updateManualInvoiceTotal);
                    });

                    const sourceField = row.querySelector('.invoice-item-source');
                    const optionField = row.querySelector('.invoice-item-option');

                    if (sourceField) {
                        sourceField.addEventListener('change', function () {
                            syncRowOptionSelector(row);
                            syncRowSelectionToInputs(row);
                            updateManualInvoiceTotal();
                        });
                    }

                    if (optionField) {
                        optionField.addEventListener('change', function () {
                            syncRowSelectionToInputs(row);
                            updateManualInvoiceTotal();
                        });
                    }

                    const removeButton = row.querySelector('.remove-invoice-item');
                    if (removeButton) {
                        removeButton.addEventListener('click', function () {
                            const rows = invoiceItemsContainer?.querySelectorAll('.invoice-item-row') || [];
                            if (rows.length <= 1) {
                                row.querySelectorAll('input').forEach((input) => {
                                    if (input.name.includes('[qty]')) {
                                        input.value = 1;
                                    } else {
                                        input.value = '';
                                    }
                                });
                                const resetSource = row.querySelector('.invoice-item-source');
                                if (resetSource) {
                                    resetSource.value = 'subscription';
                                }
                                syncRowOptionSelector(row);
                            } else {
                                row.remove();
                            }
                            refreshInvoiceItemIndexes();
                            updateManualInvoiceTotal();
                            if (window.lucide) {
                                window.lucide.createIcons();
                            }
                        });
                    }
                }

                function refreshInvoiceItemIndexes() {
                    if (!invoiceItemsContainer) {
                        return;
                    }

                    invoiceItemsContainer.querySelectorAll('.invoice-item-row').forEach((row, index) => {
                        row.querySelectorAll('input, select[name]').forEach((field) => {
                            field.name = field.name.replace(/items\[\d+\]/, `items[${index}]`);
                        });
                    });
                }

                function applyInvoiceFilters() {
                    if (!invoiceTableBody || !invoiceFilterForm) {
                        return;
                    }

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
                        if (isVisible) {
                            visibleCount++;
                        }
                    });

                    if (invoiceVisibleCount) {
                        invoiceVisibleCount.textContent = `${visibleCount} invoice tampil`;
                    }

                    invoiceTableWrapper?.classList.toggle('hidden', visibleCount === 0);
                    invoiceEmptyState?.classList.toggle('hidden', visibleCount !== 0);

                    syncInvoiceUrl();
                    updateInvoiceViewButtons();
                }

                if (toggleAdvancedFilters && advancedFiltersPanel) {
                    toggleAdvancedFilters.addEventListener('click', function () {
                        advancedFiltersPanel.classList.toggle('hidden');
                    });
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

                if (invoiceFilterForm && filterSearchInput) {
                    filterSearchInput.addEventListener('input', function () {
                        clearTimeout(filterDebounceTimer);
                        filterDebounceTimer = setTimeout(() => {
                            applyInvoiceFilters();
                        }, 400);
                    });
                }

                if (invoiceFilterForm && autoSubmitFields.length > 0) {
                    autoSubmitFields.forEach((field) => {
                        field.addEventListener('change', function () {
                            applyInvoiceFilters();
                        });
                    });
                }

                if (viewButtons.length > 0) {
                    viewButtons.forEach((button) => {
                        button.addEventListener('click', function () {
                            currentView = button.dataset.view || 'all';
                            applyInvoiceFilters();
                        });
                    });
                }

                if (resetInvoiceFilters) {
                    resetInvoiceFilters.addEventListener('click', function () {
                        invoiceFilterForm.reset();
                        currentView = 'all';
                        applyInvoiceFilters();
                    });
                }

                if (addInvoiceItemButton && invoiceItemsContainer) {
                    addInvoiceItemButton.addEventListener('click', function () {
                        const index = invoiceItemsContainer.querySelectorAll('.invoice-item-row').length;
                        const row = document.createElement('div');
                        row.className = 'invoice-item-row rounded-[1.25rem] border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/30 p-4';
                        row.innerHTML = `
                            <input type="hidden" name="items[${index}][subscription_id]" value="" class="invoice-item-subscription-id">
                            <input type="hidden" name="items[${index}][package_id]" value="" class="invoice-item-package-id">
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start">
                                <div class="md:col-span-3">
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Sumber Item</label>
                                    <select name="items[${index}][source]"
                                        class="invoice-item-source w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="subscription" selected>Layanan Pelanggan</option>
                                        <option value="package">Katalog Layanan</option>
                                        <option value="manual">Item Manual</option>
                                    </select>
                                </div>
                                <div class="md:col-span-5">
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Pilih Layanan</label>
                                    <select class="invoice-item-option w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Pilih item</option>
                                    </select>
                                    <p class="invoice-item-option-hint mt-1 text-[11px] text-slate-500 dark:text-slate-400"></p>
                                </div>
                                <div class="md:col-span-3">
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Deskripsi</label>
                                    <input type="text" name="items[${index}][description]" required
                                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="Contoh: Biaya instalasi / layanan tambahan">
                                </div>
                                <div class="md:col-span-1">
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Qty</label>
                                    <input type="number" min="1" name="items[${index}][qty]" value="1" required
                                        class="invoice-item-qty w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div class="md:col-span-3">
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nominal</label>
                                    <input type="number" min="0" step="0.01" name="items[${index}][amount]" required
                                        class="invoice-item-amount w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="0">
                                </div>
                                <div class="md:col-span-1 flex md:justify-end">
                                    <button type="button"
                                        class="remove-invoice-item mt-6 inline-flex items-center justify-center h-11 w-11 rounded-xl border border-slate-200 dark:border-slate-700 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                        invoiceItemsContainer.appendChild(row);
                        bindInvoiceItemRow(row);
                        syncRowOptionSelector(row);
                        refreshInvoiceItemIndexes();
                        updateManualInvoiceTotal();
                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                    });
                }

                if (manualInvoiceClientSelect && invoiceItemsContainer) {
                    manualInvoiceClientSelect.addEventListener('change', function () {
                        invoiceItemsContainer.querySelectorAll('.invoice-item-row').forEach((row) => {
                            syncRowOptionSelector(row);
                            syncRowSelectionToInputs(row, false);
                        });
                    });
                }

                if (sortButtons.length > 0) {
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
                }

                updateSortIcons();
                updateInvoiceViewButtons();
                applyInvoiceFilters();
                if (invoiceItemsContainer) {
                    invoiceItemsContainer.querySelectorAll('.invoice-item-row').forEach((row) => {
                        bindInvoiceItemRow(row);
                        syncRowOptionSelector(row);
                        syncRowSelectionToInputs(row, false);
                    });
                    refreshInvoiceItemIndexes();
                    updateManualInvoiceTotal();
                }

                @if ($errors->any())
                    openModal('createInvoiceDrawer');
                @endif

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
