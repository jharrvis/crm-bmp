<x-app-layout>
    @php
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
                        ])->filter()->implode(' | ')),
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
                ])->filter()->implode(' | ')),
                'description' => trim(($serviceName ? $serviceName . ' - ' : '') . $package->name),
                'amount' => (float) $package->price,
            ];
        })->values()->all();

        $oldItems = old('items', [[
            'source' => 'subscription',
            'subscription_id' => '',
            'package_id' => '',
            'description' => '',
            'qty' => 1,
            'amount' => '',
        ]]);
    @endphp

    <div class="space-y-6">
        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800 md:p-8">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Buat Invoice Manual</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Tambahkan tagihan manual untuk pelanggan tertentu beserta rincian item yang bisa dipilih dari layanan aktif atau katalog layanan.
                    </p>
                </div>
                <a
                    href="{{ route('invoices.index') }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700">
                    <i data-lucide="arrow-left" class="h-4 w-4"></i>
                    Kembali ke Daftar Invoice
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('invoices.store') }}" class="space-y-6">
            @csrf

            <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800 md:p-8">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-300">Pelanggan <span class="text-red-500">*</span></label>
                        <select name="client_id" required
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                            <option value="">Pilih pelanggan</option>
                            @foreach ($clients as $client)
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
                        <label class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-300">Tanggal Jatuh Tempo <span class="text-red-500">*</span></label>
                        <input type="date" name="due_date" required value="{{ old('due_date', now()->addDays(7)->toDateString()) }}"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                        @error('due_date')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800 md:p-8">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300">Rincian Item <span class="text-red-500">*</span></label>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Pilih layanan aktif pelanggan atau katalog layanan lain, lalu sesuaikan deskripsi dan nominal bila perlu.</p>
                    </div>
                    <button type="button" id="addInvoiceItem"
                        class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-3 py-2 text-sm font-bold text-slate-700 transition-colors hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600">
                        <i data-lucide="plus" class="h-4 w-4"></i>
                        Tambah Item
                    </button>
                </div>

                <div id="invoiceItemsContainer" class="space-y-3">
                    @foreach ($oldItems as $index => $item)
                        <div class="invoice-item-row rounded-[1.25rem] border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-700 dark:bg-slate-900/30">
                            <input type="hidden" name="items[{{ $index }}][subscription_id]" value="{{ $item['subscription_id'] ?? '' }}" class="invoice-item-subscription-id">
                            <input type="hidden" name="items[{{ $index }}][package_id]" value="{{ $item['package_id'] ?? '' }}" class="invoice-item-package-id">

                            <div class="grid grid-cols-1 items-start gap-3 md:grid-cols-12">
                                <div class="md:col-span-3">
                                    <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Sumber Item</label>
                                    <select name="items[{{ $index }}][source]"
                                        class="invoice-item-source w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                                        <option value="subscription" {{ ($item['source'] ?? 'subscription') === 'subscription' ? 'selected' : '' }}>Layanan Pelanggan</option>
                                        <option value="package" {{ ($item['source'] ?? '') === 'package' ? 'selected' : '' }}>Katalog Layanan</option>
                                        <option value="manual" {{ ($item['source'] ?? '') === 'manual' ? 'selected' : '' }}>Item Manual</option>
                                    </select>
                                </div>
                                <div class="md:col-span-5">
                                    <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Pilih Layanan</label>
                                    <select class="invoice-item-option w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                                        <option value="">Pilih item</option>
                                    </select>
                                    <p class="invoice-item-option-hint mt-1 text-[11px] text-slate-500 dark:text-slate-400"></p>
                                </div>
                                <div class="md:col-span-3">
                                    <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Deskripsi</label>
                                    <input type="text" name="items[{{ $index }}][description]" value="{{ $item['description'] ?? '' }}" required
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                                        placeholder="Contoh: Biaya instalasi / layanan tambahan">
                                </div>
                                <div class="md:col-span-1">
                                    <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Qty</label>
                                    <input type="number" min="1" name="items[{{ $index }}][qty]" value="{{ $item['qty'] ?? 1 }}" required
                                        class="invoice-item-qty w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                                </div>
                                <div class="md:col-span-3">
                                    <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Nominal</label>
                                    <input type="number" min="0" step="0.01" name="items[{{ $index }}][amount]" value="{{ $item['amount'] ?? '' }}" required
                                        class="invoice-item-amount w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                                        placeholder="0">
                                </div>
                                <div class="flex md:col-span-1 md:justify-end">
                                    <button type="button"
                                        class="remove-invoice-item mt-6 inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 text-red-500 transition-colors hover:bg-red-50 dark:border-slate-700 dark:hover:bg-red-900/20">
                                        <i data-lucide="trash-2" class="h-4 w-4"></i>
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

            <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800 md:p-8">
                <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-300">Catatan</label>
                        <textarea name="notes" rows="4"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                            placeholder="Catatan tambahan untuk invoice ini">{{ old('notes') }}</textarea>
                    </div>
                    <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50/70 p-5 dark:border-slate-700 dark:bg-slate-900/30">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Estimasi Total</p>
                        <p id="manualInvoiceTotal" class="mt-2 text-2xl font-black text-slate-900 dark:text-white">Rp 0</p>
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Total dihitung dari penjumlahan qty x nominal setiap item.</p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <a
                        href="{{ route('invoices.index') }}"
                        class="rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-bold text-slate-700 transition-colors hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600">
                        Batal
                    </a>
                    <button type="submit"
                        class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white transition-colors hover:bg-blue-700">
                        Simpan Invoice
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const invoiceItemsContainer = document.getElementById('invoiceItemsContainer');
                const addInvoiceItemButton = document.getElementById('addInvoiceItem');
                const manualInvoiceTotal = document.getElementById('manualInvoiceTotal');
                const manualInvoiceClientSelect = document.querySelector('select[name="client_id"]');
                const clientSubscriptionCatalog = @js($manualInvoiceClientOptions);
                const packageCatalog = @js($manualInvoicePackageOptions);

                function formatCurrency(value) {
                    return new Intl.NumberFormat('id-ID').format(Number(value || 0));
                }

                function updateManualInvoiceTotal() {
                    let total = 0;

                    invoiceItemsContainer.querySelectorAll('.invoice-item-row').forEach((row) => {
                        const qty = Number(row.querySelector('.invoice-item-qty')?.value || 0);
                        const amount = Number(row.querySelector('.invoice-item-amount')?.value || 0);
                        total += qty * amount;
                    });

                    manualInvoiceTotal.textContent = `Rp ${formatCurrency(total)}`;
                }

                function getRowCatalog(row) {
                    const source = row.querySelector('.invoice-item-source')?.value || 'subscription';
                    const clientId = manualInvoiceClientSelect?.value || '';

                    if (source === 'subscription') {
                        return clientSubscriptionCatalog[clientId] || [];
                    }

                    if (source === 'package') {
                        return packageCatalog;
                    }

                    return [];
                }

                function syncRowOptionSelector(row) {
                    const source = row.querySelector('.invoice-item-source')?.value || 'subscription';
                    const optionSelect = row.querySelector('.invoice-item-option');
                    const optionHint = row.querySelector('.invoice-item-option-hint');
                    const catalog = getRowCatalog(row);

                    if (!optionSelect) {
                        return;
                    }

                    optionSelect.innerHTML = '<option value="">Pilih item</option>';

                    if (source === 'manual') {
                        optionSelect.disabled = true;
                        optionHint.textContent = 'Deskripsi dan nominal diisi manual.';
                        return;
                    }

                    optionSelect.disabled = false;

                    if (catalog.length === 0) {
                        optionHint.textContent = source === 'subscription'
                            ? 'Pelanggan ini belum memiliki layanan aktif.'
                            : 'Tidak ada katalog layanan aktif.';
                        return;
                    }

                    catalog.forEach((option) => {
                        const optionElement = document.createElement('option');
                        optionElement.value = String(option.id);
                        optionElement.textContent = option.label;
                        optionSelect.appendChild(optionElement);
                    });

                    optionHint.textContent = source === 'subscription'
                        ? 'Pilih layanan aktif pelanggan untuk mengisi deskripsi dan nominal otomatis.'
                        : 'Pilih katalog layanan untuk mengisi item lebih cepat.';
                }

                function syncRowSelectionToInputs(row, preserveManualValues = true) {
                    const source = row.querySelector('.invoice-item-source')?.value || 'subscription';
                    const optionSelect = row.querySelector('.invoice-item-option');
                    const descriptionInput = row.querySelector('input[name$="[description]"]');
                    const amountInput = row.querySelector('.invoice-item-amount');
                    const subscriptionInput = row.querySelector('.invoice-item-subscription-id');
                    const packageInput = row.querySelector('.invoice-item-package-id');
                    const selectedValue = optionSelect?.value || '';
                    const catalog = getRowCatalog(row);
                    const selected = catalog.find((entry) => String(entry.id) === String(selectedValue));

                    if (subscriptionInput) {
                        subscriptionInput.value = source === 'subscription' && selected ? selected.id : '';
                    }

                    if (packageInput) {
                        packageInput.value = source === 'package' && selected ? selected.id : '';
                    }

                    if (source === 'manual') {
                        if (!preserveManualValues) {
                            descriptionInput.value = '';
                            amountInput.value = '';
                        }
                        return;
                    }

                    if (!selected) {
                        if (!preserveManualValues) {
                            descriptionInput.value = '';
                            amountInput.value = '';
                        }
                        return;
                    }

                    descriptionInput.value = selected.description || '';
                    amountInput.value = selected.amount ?? '';
                }

                function refreshInvoiceItemIndexes() {
                    invoiceItemsContainer.querySelectorAll('.invoice-item-row').forEach((row, index) => {
                        row.querySelectorAll('input, select[name]').forEach((field) => {
                            field.name = field.name.replace(/items\[\d+\]/, `items[${index}]`);
                        });
                    });
                }

                function bindInvoiceItemRow(row) {
                    const qtyInput = row.querySelector('.invoice-item-qty');
                    const amountInput = row.querySelector('.invoice-item-amount');
                    const sourceField = row.querySelector('.invoice-item-source');
                    const optionField = row.querySelector('.invoice-item-option');
                    const removeButton = row.querySelector('.remove-invoice-item');

                    qtyInput?.addEventListener('input', updateManualInvoiceTotal);
                    amountInput?.addEventListener('input', updateManualInvoiceTotal);

                    sourceField?.addEventListener('change', function () {
                        syncRowOptionSelector(row);
                        syncRowSelectionToInputs(row, false);
                        updateManualInvoiceTotal();
                    });

                    optionField?.addEventListener('change', function () {
                        syncRowSelectionToInputs(row);
                        updateManualInvoiceTotal();
                    });

                    removeButton?.addEventListener('click', function () {
                        const rows = invoiceItemsContainer.querySelectorAll('.invoice-item-row');

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
                            syncRowSelectionToInputs(row, false);
                        } else {
                            row.remove();
                            refreshInvoiceItemIndexes();
                        }

                        updateManualInvoiceTotal();
                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                    });
                }

                addInvoiceItemButton?.addEventListener('click', function () {
                    const index = invoiceItemsContainer.querySelectorAll('.invoice-item-row').length;
                    const row = document.createElement('div');
                    row.className = 'invoice-item-row rounded-[1.25rem] border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-700 dark:bg-slate-900/30';
                    row.innerHTML = `
                        <input type="hidden" name="items[${index}][subscription_id]" value="" class="invoice-item-subscription-id">
                        <input type="hidden" name="items[${index}][package_id]" value="" class="invoice-item-package-id">
                        <div class="grid grid-cols-1 items-start gap-3 md:grid-cols-12">
                            <div class="md:col-span-3">
                                <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Sumber Item</label>
                                <select name="items[${index}][source]" class="invoice-item-source w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                                    <option value="subscription" selected>Layanan Pelanggan</option>
                                    <option value="package">Katalog Layanan</option>
                                    <option value="manual">Item Manual</option>
                                </select>
                            </div>
                            <div class="md:col-span-5">
                                <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Pilih Layanan</label>
                                <select class="invoice-item-option w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                                    <option value="">Pilih item</option>
                                </select>
                                <p class="invoice-item-option-hint mt-1 text-[11px] text-slate-500 dark:text-slate-400"></p>
                            </div>
                            <div class="md:col-span-3">
                                <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Deskripsi</label>
                                <input type="text" name="items[${index}][description]" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white" placeholder="Contoh: Biaya instalasi / layanan tambahan">
                            </div>
                            <div class="md:col-span-1">
                                <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Qty</label>
                                <input type="number" min="1" name="items[${index}][qty]" value="1" required class="invoice-item-qty w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                            </div>
                            <div class="md:col-span-3">
                                <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Nominal</label>
                                <input type="number" min="0" step="0.01" name="items[${index}][amount]" required class="invoice-item-amount w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white" placeholder="0">
                            </div>
                            <div class="flex md:col-span-1 md:justify-end">
                                <button type="button" class="remove-invoice-item mt-6 inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 text-red-500 transition-colors hover:bg-red-50 dark:border-slate-700 dark:hover:bg-red-900/20">
                                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                                </button>
                            </div>
                        </div>
                    `;
                    invoiceItemsContainer.appendChild(row);
                    bindInvoiceItemRow(row);
                    syncRowOptionSelector(row);
                    syncRowSelectionToInputs(row, false);
                    refreshInvoiceItemIndexes();
                    updateManualInvoiceTotal();
                    if (window.lucide) {
                        window.lucide.createIcons();
                    }
                });

                manualInvoiceClientSelect?.addEventListener('change', function () {
                    invoiceItemsContainer.querySelectorAll('.invoice-item-row').forEach((row) => {
                        syncRowOptionSelector(row);
                        syncRowSelectionToInputs(row, false);
                    });
                    updateManualInvoiceTotal();
                });

                invoiceItemsContainer.querySelectorAll('.invoice-item-row').forEach((row) => {
                    bindInvoiceItemRow(row);
                    syncRowOptionSelector(row);
                    syncRowSelectionToInputs(row, false);
                });

                refreshInvoiceItemIndexes();
                updateManualInvoiceTotal();

                if (window.lucide) {
                    window.lucide.createIcons();
                }
            });
        </script>
    @endpush
</x-app-layout>
