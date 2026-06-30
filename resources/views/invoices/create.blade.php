<x-app-layout>
    @php
        $manualInvoiceClients = $clients->map(function ($client) {
            $contact = $client->primaryContact ?: $client->contacts->first();

            return [
                'id' => $client->id,
                'name' => $client->name,
                'client_code' => $client->client_code,
                'address' => trim(collect([$client->address, $client->city])->filter()->implode(', ')),
                'email' => $contact?->email,
                'phone' => $contact?->phone,
                'whatsapp' => $contact?->whatsapp ?: $contact?->phone,
                'subscriptions' => $client->subscriptions->map(function ($subscription) {
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
        })->values();

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
        })->values();

        $invoiceItems = $invoice?->items->map(function ($item) {
            return [
                'source' => $item->subscription_id ? 'subscription' : 'manual',
                'subscription_id' => $item->subscription_id,
                'package_id' => '',
                'description' => $item->description,
                'qty' => $item->qty,
                'amount' => $item->amount,
            ];
        })->values()->all() ?? [[
            'source' => 'subscription',
            'subscription_id' => '',
            'package_id' => '',
            'description' => '',
            'qty' => 1,
            'amount' => '',
        ]];

        $oldItems = old('items', $invoiceItems);

        $defaultClientId = old('client_id', $invoice?->client_id);
        $selectedClient = $manualInvoiceClients->firstWhere('id', (int) $defaultClientId);
        $selectedSignatureMode = old('signature_mode', $invoice?->signature_path ? 'existing' : 'none');
        $selectedExistingSignature = old('existing_signature', $invoice?->signature_path);
        $selectedDueMode = old('due_date_mode', '7');
        $oldSendChannels = collect(old('send_channels', []))->map(fn ($value) => (string) $value)->all();
        $existingSignaturePreview = collect($existingSignatures)->firstWhere('path', $selectedExistingSignature);
        $formAction = $invoice ? route('invoices.update', $invoice) : route('invoices.store');
        $formTitle = $invoice ? 'Edit Invoice Manual' : 'Buat Invoice Manual';
        $formSubtitle = $invoice
            ? 'Perbarui rincian invoice, tanda tangan, dan opsi kirim tanpa harus membuat ulang dari awal.'
            : 'Buat invoice satu kali dengan item yang lebih ringkas, pengaturan pajak, tanda tangan, dan opsi kirim langsung.';
    @endphp

    <div class="space-y-6">
        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800 md:p-8">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-white">{{ $formTitle }}</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ $formSubtitle }}
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

        <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" id="manualInvoiceForm" class="space-y-6">
            @csrf
            @if($invoice)
                @method('PUT')
            @endif
            <input type="hidden" name="submit_action" id="submitActionField" value="{{ old('submit_action', 'confirm') }}">

            <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800 md:p-8">
                <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
                    <div class="xl:col-span-1">
                        <label class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-300">Pelanggan <span class="text-red-500">*</span></label>
                        <div class="relative" id="clientCombobox">
                            <input type="hidden" name="client_id" id="clientIdField" value="{{ $defaultClientId }}">
                            <input
                                type="text"
                                id="clientSearchInput"
                                value="{{ $selectedClient ? $selectedClient['name'] . ' (' . $selectedClient['client_code'] . ')' : '' }}"
                                autocomplete="off"
                                placeholder="Cari pelanggan atau client code"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 pr-12 text-slate-800 outline-none transition focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                            <button type="button" id="clientDropdownToggle" class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-slate-400">
                                <i data-lucide="chevrons-up-down" class="h-4 w-4"></i>
                            </button>
                            <div id="clientDropdown" class="absolute left-0 right-0 top-[calc(100%+8px)] z-30 hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                                <div class="max-h-80 overflow-y-auto p-2" id="clientDropdownOptions"></div>
                            </div>
                        </div>
                        <div id="selectedClientMeta" class="mt-3 rounded-[1.25rem] border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-900/30 dark:text-slate-300">
                            @if($selectedClient)
                                <div class="font-semibold text-slate-800 dark:text-white">{{ $selectedClient['client_code'] }}</div>
                                <div class="mt-1">{{ $selectedClient['address'] ?: 'Alamat pelanggan belum tersedia.' }}</div>
                                <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                    Email: {{ $selectedClient['email'] ?: '-' }} · WhatsApp: {{ $selectedClient['whatsapp'] ?: '-' }}
                                </div>
                            @else
                                Pilih pelanggan untuk memunculkan langganan aktif dan kanal pengiriman.
                            @endif
                        </div>
                        @error('client_id')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-300">Tanggal Invoice <span class="text-red-500">*</span></label>
                        <input
                            type="date"
                            name="invoice_date"
                            id="invoiceDateField"
                            required
                            value="{{ old('invoice_date', optional($invoice?->invoice_date)->toDateString() ?? now()->toDateString()) }}"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-800 outline-none transition focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                        @error('invoice_date')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-300">Jatuh Tempo <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-[190px_minmax(0,1fr)]">
                            <select
                                name="due_date_mode"
                                id="dueDateModeField"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-800 outline-none transition focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                                <option value="7" {{ $selectedDueMode === '7' ? 'selected' : '' }}>7 hari</option>
                                <option value="14" {{ $selectedDueMode === '14' ? 'selected' : '' }}>14 hari</option>
                                <option value="30" {{ $selectedDueMode === '30' ? 'selected' : '' }}>30 hari</option>
                                <option value="custom" {{ $selectedDueMode === 'custom' ? 'selected' : '' }}>Custom tanggal</option>
                            </select>
                            <input
                                type="date"
                                name="due_date"
                                id="dueDateField"
                                required
                                value="{{ old('due_date', optional($invoice?->due_date)->toDateString() ?? now()->addDays(7)->toDateString()) }}"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-800 outline-none transition focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                        </div>
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                            Pilih preset jatuh tempo atau ganti ke tanggal custom.
                        </p>
                        @error('due_date')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800 md:p-8">
                <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300">Rincian Item <span class="text-red-500">*</span></label>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Setiap item dibuat ringkas dalam satu baris: sumber, pilihan layanan, deskripsi, qty, harga, dan total baris.</p>
                    </div>
                    <button type="button" id="addInvoiceItem"
                        class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600">
                        <i data-lucide="plus" class="h-4 w-4"></i>
                        Tambah Item
                    </button>
                </div>

                <div class="space-y-3" id="invoiceItemsContainer">
                    @foreach ($oldItems as $index => $item)
                        <div class="invoice-item-row rounded-[1.35rem] border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-700 dark:bg-slate-900/30">
                            <input type="hidden" name="items[{{ $index }}][subscription_id]" value="{{ $item['subscription_id'] ?? '' }}" class="invoice-item-subscription-id">
                            <input type="hidden" name="items[{{ $index }}][package_id]" value="{{ $item['package_id'] ?? '' }}" class="invoice-item-package-id">

                            <div class="grid grid-cols-1 gap-3 lg:grid-cols-[170px_320px_90px_150px_150px_48px] lg:items-start">
                                <div>
                                    <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Sumber</label>
                                    <select name="items[{{ $index }}][source]"
                                        class="invoice-item-source w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                                        <option value="subscription" {{ ($item['source'] ?? 'subscription') === 'subscription' ? 'selected' : '' }}>Layanan Pelanggan</option>
                                        <option value="package" {{ ($item['source'] ?? '') === 'package' ? 'selected' : '' }}>Katalog Layanan</option>
                                        <option value="manual" {{ ($item['source'] ?? '') === 'manual' ? 'selected' : '' }}>Item Manual</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Pilih Item</label>
                                    <select class="invoice-item-option w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                                        <option value="">Pilih item</option>
                                    </select>
                                    <p class="invoice-item-option-hint mt-1 text-[11px] text-slate-500 dark:text-slate-400"></p>
                                </div>
                                <div>
                                    <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Qty</label>
                                    <input type="number" min="1" name="items[{{ $index }}][qty]" value="{{ $item['qty'] ?? 1 }}" required
                                        class="invoice-item-qty w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                                </div>
                                <div>
                                    <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Harga</label>
                                    <input type="number" min="0" step="0.01" name="items[{{ $index }}][amount]" value="{{ $item['amount'] ?? '' }}" required
                                        class="invoice-item-amount w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                                        placeholder="0">
                                </div>
                                <div>
                                    <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Jumlah</label>
                                    <div class="invoice-item-total rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-800 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                                        Rp 0
                                    </div>
                                </div>
                                <button type="button"
                                    class="remove-invoice-item inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 text-red-500 transition hover:bg-red-50 dark:border-slate-700 dark:hover:bg-red-900/20">
                                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                                </button>
                                <div class="lg:col-span-5">
                                    <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Deskripsi</label>
                                    <textarea name="items[{{ $index }}][description]" rows="2" required
                                        class="invoice-item-description w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                                        placeholder="Contoh: Biaya instalasi / layanan tambahan">{{ $item['description'] ?? '' }}</textarea>
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
                <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
                    <div class="rounded-[1.5rem] border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
                        <label class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-300">Catatan</label>
                        <textarea name="notes" rows="7"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-white"
                            placeholder="Catatan tambahan untuk invoice ini">{{ old('notes', $invoice?->notes) }}</textarea>
                    </div>

                    <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50/80 p-5 dark:border-slate-700 dark:bg-slate-900/30">
                        <div class="flex flex-col gap-4">
                            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <p class="text-sm font-bold text-slate-800 dark:text-white">Ringkasan Tagihan</p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Subtotal diambil dari jumlah seluruh item. Pajak dan diskon diperhitungkan sebelum total akhir.</p>
                                </div>
                                <label class="inline-flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200">
                                    <input type="checkbox" name="uses_tax" id="usesTaxField" value="1" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" {{ old('uses_tax', $invoice?->uses_tax) ? 'checked' : '' }}>
                                    Gunakan PPN 11%
                                </label>
                            </div>

                            <div class="space-y-3">
                                <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm dark:border-slate-600 dark:bg-slate-800">
                                    <span class="font-semibold text-slate-500 dark:text-slate-300">Subtotal</span>
                                    <span id="invoiceSubtotalValue" class="font-bold text-slate-900 dark:text-white">Rp 0</span>
                                </div>
                                <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm dark:border-slate-600 dark:bg-slate-800">
                                    <span class="font-semibold text-slate-500 dark:text-slate-300">PPN (Pajak)</span>
                                    <span id="invoiceTaxValue" class="font-bold text-slate-900 dark:text-white">Rp 0</span>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 dark:border-slate-600 dark:bg-slate-800">
                                    <label class="mb-2 block text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Discount (Opsional)</label>
                                    <div class="flex items-center gap-3">
                                        <input type="number" min="0" step="0.01" name="discount_amount" id="discountAmountField"
                                            value="{{ old('discount_amount', $invoice?->discount_amount ?? 0) }}"
                                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-white"
                                            placeholder="0">
                                        <span id="invoiceDiscountValue" class="whitespace-nowrap text-sm font-bold text-slate-900 dark:text-white">Rp 0</span>
                                    </div>
                                </div>
                                <div class="rounded-[1.5rem] bg-blue-600 px-5 py-5 text-white">
                                    <div class="text-xs font-bold uppercase tracking-[0.18em] text-blue-100">Total</div>
                                    <div id="invoiceGrandTotalValue" class="mt-2 text-3xl font-black">Rp 0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 border-t border-slate-200 pt-6 dark:border-slate-700">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div class="min-w-0">
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300">Tanda Tangan dan Meterai (Opsional)</label>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Klik kotak di bawah untuk memilih tanda tangan yang sudah ada atau upload baru.</p>
                        </div>
                        <input type="radio" name="signature_mode" value="none" class="hidden" {{ $selectedSignatureMode === 'none' ? 'checked' : '' }}>
                        <input type="radio" name="signature_mode" value="existing" class="hidden" {{ $selectedSignatureMode === 'existing' ? 'checked' : '' }}>
                        <input type="radio" name="signature_mode" value="upload" class="hidden" {{ $selectedSignatureMode === 'upload' ? 'checked' : '' }}>
                    </div>

                    <button type="button" id="signaturePickerButton" onclick="openModal('signaturePickerModal')" class="mt-4 flex w-full items-center justify-center rounded-[1.5rem] border border-dashed border-blue-300 bg-slate-50/80 px-6 py-8 text-center transition hover:border-blue-500 hover:bg-blue-50/50 dark:border-slate-600 dark:bg-slate-900/30 dark:hover:border-blue-400 dark:hover:bg-blue-500/10">
                        <div id="signaturePreviewState" class="flex flex-col items-center gap-3">
                            @if($selectedSignatureMode !== 'none' && $existingSignaturePreview)
                                <div class="flex h-28 w-full max-w-xs items-center justify-center">
                                    <img src="{{ $existingSignaturePreview['url'] }}" alt="Signature preview" class="max-h-full max-w-full object-contain">
                                </div>
                                <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ \Illuminate\Support\Str::headline($existingSignaturePreview['name']) }}</div>
                            @elseif($selectedSignatureMode === 'upload')
                                <div class="rounded-full bg-blue-100 p-3 text-blue-600 dark:bg-blue-500/15 dark:text-blue-300">
                                    <i data-lucide="image-plus" class="h-6 w-6"></i>
                                </div>
                                <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">Upload tanda tangan baru</div>
                            @else
                                <div class="rounded-full bg-blue-100 p-3 text-blue-600 dark:bg-blue-500/15 dark:text-blue-300">
                                    <i data-lucide="upload" class="h-6 w-6"></i>
                                </div>
                                <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">Pilih atau unggah tanda tangan</div>
                            @endif
                        </div>
                    </button>
                    @error('existing_signature')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                    @error('signature_upload')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <a
                        href="{{ route('invoices.index') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600">
                        Batal
                    </a>
                    <div class="relative inline-flex" id="saveActionsDropdown">
                        <span id="saveActionPreview" class="hidden"></span>
                        <button type="button" id="savePrimaryButton"
                            class="inline-flex items-center gap-2 rounded-l-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-blue-700">
                            <i data-lucide="save" class="h-4 w-4"></i>
                            <span id="savePrimaryButtonLabel">Simpan & Konfirmasi</span>
                        </button>
                        <button type="button" id="saveDropdownToggle"
                            class="inline-flex items-center rounded-r-xl border-l border-white/15 bg-blue-600 px-3 text-white transition hover:bg-blue-700">
                            <i data-lucide="chevron-down" class="h-4 w-4"></i>
                        </button>
                        <div id="saveDropdownMenu" class="absolute right-0 top-[calc(100%+10px)] z-30 hidden w-64 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                            <button type="button" data-save-action="draft" class="flex w-full items-start gap-3 px-4 py-3 text-left transition hover:bg-slate-50 dark:hover:bg-slate-800">
                                <i data-lucide="file-pen-line" class="mt-0.5 h-4 w-4 text-slate-400"></i>
                                <span>
                                    <span class="block text-sm font-bold text-slate-800 dark:text-white">Simpan Draft</span>
                                    <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">Simpan sebagai draft tanpa finalisasi.</span>
                                </span>
                            </button>
                            <button type="button" data-save-action="confirm" class="flex w-full items-start gap-3 border-t border-slate-200 px-4 py-3 text-left transition hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">
                                <i data-lucide="badge-check" class="mt-0.5 h-4 w-4 text-slate-400"></i>
                                <span>
                                    <span class="block text-sm font-bold text-slate-800 dark:text-white">Simpan & Konfirmasi</span>
                                    <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">Finalkan invoice tanpa mengirim ke pelanggan.</span>
                                </span>
                            </button>
                            <button type="button" data-save-action="send" class="flex w-full items-start gap-3 border-t border-slate-200 px-4 py-3 text-left transition hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">
                                <i data-lucide="send" class="mt-0.5 h-4 w-4 text-slate-400"></i>
                                <span>
                                    <span class="block text-sm font-bold text-slate-800 dark:text-white">Simpan & Kirim</span>
                                    <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">Pilih kanal kirim dan edit template email / WhatsApp.</span>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div id="signaturePickerModal" class="fixed inset-0 z-[90] hidden">
        <div id="signaturePickerModalBackdrop" class="modal-backdrop absolute inset-0 bg-slate-900/55 opacity-0 transition-opacity duration-300"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4 sm:p-6">
            <div id="signaturePickerModalPanel" class="modal-panel w-full max-w-3xl scale-95 opacity-0 overflow-hidden rounded-[2rem] bg-white shadow-2xl transition-all duration-300 dark:bg-slate-800">
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-5 dark:border-slate-700 sm:px-8">
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-[0.2em] text-blue-500">Tanda Tangan</div>
                        <h2 class="mt-2 text-2xl font-bold text-slate-800 dark:text-white">Pilih atau Upload Tanda Tangan</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">File baru akan disimpan untuk pemakaian berikutnya.</p>
                    </div>
                    <button type="button" onclick="closeModal('signaturePickerModal')"
                        class="flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 text-slate-500 transition hover:bg-slate-50 hover:text-slate-700 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white">
                        <i data-lucide="x" class="h-5 w-5"></i>
                    </button>
                </div>
                <div class="max-h-[72vh] overflow-y-auto px-6 py-6 sm:px-8">
                    <div class="mb-4">
                        <button type="button" id="clearSignatureButton" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700">
                            <i data-lucide="eraser" class="h-4 w-4"></i>
                            Tanpa tanda tangan
                        </button>
                    </div>
                    <div id="existingSignatureSection">
                        @if (count($existingSignatures) > 0)
                            <div class="grid gap-3 md:grid-cols-2">
                                @foreach ($existingSignatures as $signature)
                                    <button type="button" data-signature-select="{{ $signature['path'] }}" data-signature-url="{{ $signature['url'] }}" data-signature-name="{{ \Illuminate\Support\Str::headline($signature['name']) }}"
                                        class="rounded-[1.25rem] border border-slate-200 bg-white p-4 text-left transition hover:border-blue-400 hover:ring-2 hover:ring-blue-200 dark:border-slate-700 dark:bg-slate-900/30 dark:hover:ring-blue-500/30">
                                        <input type="radio" name="existing_signature" value="{{ $signature['path'] }}" class="hidden" {{ $selectedExistingSignature === $signature['path'] ? 'checked' : '' }}>
                                        <div class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-200">{{ \Illuminate\Support\Str::headline($signature['name']) }}</div>
                                        <div class="flex h-24 items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800">
                                            <img src="{{ $signature['url'] }}" alt="{{ $signature['name'] }}" class="max-h-full max-w-full object-contain">
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div id="uploadSignatureSection" class="mt-6 border-t border-slate-200 pt-5 dark:border-slate-700">
                        <label class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-300">Upload Baru</label>
                        <input
                            type="file"
                            name="signature_upload"
                            accept="image/png,image/jpeg,image/webp"
                            class="w-full rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-lg file:border-0 file:bg-slate-200 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-300 dark:border-slate-600 dark:bg-slate-900/30 dark:text-slate-200 dark:file:bg-slate-700 dark:file:text-slate-200">
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">PNG/JPG/WEBP, maksimal 2 MB.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="invoiceSendModal" class="fixed inset-0 z-[90] hidden">
        <div id="invoiceSendModalBackdrop" class="modal-backdrop absolute inset-0 bg-slate-900/55 opacity-0 transition-opacity duration-300"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4 sm:p-6">
            <div id="invoiceSendModalPanel" class="modal-panel w-full max-w-3xl scale-95 opacity-0 overflow-hidden rounded-[2rem] bg-white shadow-2xl transition-all duration-300 dark:bg-slate-800">
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-5 dark:border-slate-700 sm:px-8">
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-[0.2em] text-blue-500">Kirim Invoice</div>
                        <h2 class="mt-2 text-2xl font-bold text-slate-800 dark:text-white">Pilih Kanal Pengiriman</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Anda masih bisa mengedit template email dan WhatsApp sebelum invoice disimpan.</p>
                    </div>
                    <button type="button" onclick="closeModal('invoiceSendModal')"
                        class="flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 text-slate-500 transition hover:bg-slate-50 hover:text-slate-700 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white">
                        <i data-lucide="x" class="h-5 w-5"></i>
                    </button>
                </div>

                <div class="max-h-[72vh] overflow-y-auto px-6 py-6 sm:px-8">
                    <div class="grid gap-6 lg:grid-cols-2">
                        <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50/80 p-5 dark:border-slate-700 dark:bg-slate-900/30">
                            <label class="flex items-start gap-3">
                                <input form="manualInvoiceForm" type="checkbox" name="send_channels[]" value="email" id="sendEmailCheckbox" class="mt-0.5 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" {{ in_array('email', $oldSendChannels, true) ? 'checked' : '' }}>
                                <span>
                                    <span class="block text-sm font-bold text-slate-800 dark:text-white">Kirim via Email</span>
                                    <span id="sendEmailMeta" class="mt-1 block text-xs text-slate-500 dark:text-slate-400">Email kontak pelanggan akan dipakai sebagai tujuan kirim.</span>
                                </span>
                            </label>

                            <div id="sendEmailFields" class="mt-4 {{ in_array('email', $oldSendChannels, true) ? '' : 'hidden' }} space-y-3">
                                <div>
                                    <label class="mb-1 block text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Subjek Email</label>
                                    <input form="manualInvoiceForm" type="text" name="email_subject" id="emailSubjectField"
                                        value="{{ old('email_subject') }}"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                                    @error('email_subject')
                                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Isi Email</label>
                                    <textarea form="manualInvoiceForm" name="email_body" id="emailBodyField" rows="8"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white">{{ old('email_body') }}</textarea>
                                    @error('email_body')
                                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50/80 p-5 dark:border-slate-700 dark:bg-slate-900/30">
                            <label class="flex items-start gap-3">
                                <input form="manualInvoiceForm" type="checkbox" name="send_channels[]" value="whatsapp" id="sendWhatsappCheckbox" class="mt-0.5 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" {{ in_array('whatsapp', $oldSendChannels, true) ? 'checked' : '' }}>
                                <span>
                                    <span class="block text-sm font-bold text-slate-800 dark:text-white">Kirim via WhatsApp</span>
                                    <span id="sendWhatsappMeta" class="mt-1 block text-xs text-slate-500 dark:text-slate-400">Link `wa.me` akan dibuka menggunakan nomor kontak pelanggan.</span>
                                </span>
                            </label>

                            <div id="sendWhatsappFields" class="mt-4 {{ in_array('whatsapp', $oldSendChannels, true) ? '' : 'hidden' }} space-y-3">
                                <div>
                                    <label class="mb-1 block text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Pesan WhatsApp</label>
                                    <textarea form="manualInvoiceForm" name="whatsapp_body" id="whatsappBodyField" rows="10"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white">{{ old('whatsapp_body') }}</textarea>
                                    @error('whatsapp_body')
                                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    @error('send_channels')
                        <p class="mt-4 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 dark:border-slate-700 dark:bg-slate-900/40 sm:flex-row sm:items-center sm:justify-between sm:px-8">
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        WhatsApp hanya aktif jika pelanggan memiliki nomor WA. Email hanya aktif jika kontak pelanggan memiliki email.
                    </p>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="closeModal('invoiceSendModal')"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-white dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700">
                            Batal
                        </button>
                        <button type="button" id="submitSendAction"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                            <i data-lucide="send" class="h-4 w-4"></i>
                            Simpan & Kirim
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const clients = @js($manualInvoiceClients);
                const packageCatalog = @js($manualInvoicePackageOptions);
                const existingSignatureCount = @js(count($existingSignatures));
                const oldItems = @js($oldItems);

                const clientIdField = document.getElementById('clientIdField');
                const clientSearchInput = document.getElementById('clientSearchInput');
                const clientDropdown = document.getElementById('clientDropdown');
                const clientDropdownOptions = document.getElementById('clientDropdownOptions');
                const clientDropdownToggle = document.getElementById('clientDropdownToggle');
                const selectedClientMeta = document.getElementById('selectedClientMeta');
                const invoiceDateField = document.getElementById('invoiceDateField');
                const dueDateModeField = document.getElementById('dueDateModeField');
                const dueDateField = document.getElementById('dueDateField');
                const usesTaxField = document.getElementById('usesTaxField');
                const discountAmountField = document.getElementById('discountAmountField');
                const invoiceSubtotalValue = document.getElementById('invoiceSubtotalValue');
                const invoiceTaxValue = document.getElementById('invoiceTaxValue');
                const invoiceDiscountValue = document.getElementById('invoiceDiscountValue');
                const invoiceGrandTotalValue = document.getElementById('invoiceGrandTotalValue');
                const invoiceItemsContainer = document.getElementById('invoiceItemsContainer');
                const addInvoiceItemButton = document.getElementById('addInvoiceItem');
                const submitActionField = document.getElementById('submitActionField');
                const savePrimaryButton = document.getElementById('savePrimaryButton');
                const savePrimaryButtonLabel = document.getElementById('savePrimaryButtonLabel');
                const saveDropdownToggle = document.getElementById('saveDropdownToggle');
                const saveDropdownMenu = document.getElementById('saveDropdownMenu');
                const saveActionPreview = document.getElementById('saveActionPreview');
                const saveActionButtons = document.querySelectorAll('[data-save-action]');
                const manualInvoiceForm = document.getElementById('manualInvoiceForm');
                const submitSendAction = document.getElementById('submitSendAction');
                const sendEmailCheckbox = document.getElementById('sendEmailCheckbox');
                const sendWhatsappCheckbox = document.getElementById('sendWhatsappCheckbox');
                const sendEmailFields = document.getElementById('sendEmailFields');
                const sendWhatsappFields = document.getElementById('sendWhatsappFields');
                const sendEmailMeta = document.getElementById('sendEmailMeta');
                const sendWhatsappMeta = document.getElementById('sendWhatsappMeta');
                const emailSubjectField = document.getElementById('emailSubjectField');
                const emailBodyField = document.getElementById('emailBodyField');
                const whatsappBodyField = document.getElementById('whatsappBodyField');
                const signaturePickerButton = document.getElementById('signaturePickerButton');
                const signaturePreviewState = document.getElementById('signaturePreviewState');
                const clearSignatureButton = document.getElementById('clearSignatureButton');
                const existingSignatureSection = document.getElementById('existingSignatureSection');
                const uploadSignatureSection = document.getElementById('uploadSignatureSection');
                const signatureUploadInput = document.querySelector('#uploadSignatureSection input[name="signature_upload"]');

                let activeSaveAction = submitActionField.value || 'confirm';

                function formatCurrency(value) {
                    return `Rp ${new Intl.NumberFormat('id-ID').format(Number(value || 0))}`;
                }

                function getSelectedClient() {
                    const clientId = Number(clientIdField.value || 0);
                    return clients.find((entry) => Number(entry.id) === clientId) || null;
                }

                function renderClientMeta(client) {
                    if (!client) {
                        selectedClientMeta.innerHTML = 'Pilih pelanggan untuk memunculkan langganan aktif dan kanal pengiriman.';
                        return;
                    }

                    selectedClientMeta.innerHTML = `
                        <div class="font-semibold text-slate-800 dark:text-white">${client.client_code}</div>
                        <div class="mt-1">${client.address || 'Alamat pelanggan belum tersedia.'}</div>
                        <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                            Email: ${client.email || '-'} · WhatsApp: ${client.whatsapp || '-'}
                        </div>
                    `;
                }

                function renderClientOptions(query = '') {
                    const search = query.trim().toLowerCase();
                    const filtered = clients.filter((client) => {
                        if (!search) return true;
                        return [client.name, client.client_code, client.address].filter(Boolean).some((value) => String(value).toLowerCase().includes(search));
                    });

                    if (filtered.length === 0) {
                        clientDropdownOptions.innerHTML = '<div class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">Pelanggan tidak ditemukan.</div>';
                        return;
                    }

                    clientDropdownOptions.innerHTML = filtered.map((client) => `
                        <button type="button" data-client-option="${client.id}" class="flex w-full items-start gap-3 rounded-xl px-3 py-3 text-left transition hover:bg-slate-50 dark:hover:bg-slate-800">
                            <div class="min-w-0">
                                <div class="font-semibold text-slate-800 dark:text-white">${client.name}</div>
                                <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">${client.client_code} · ${client.address || 'Alamat belum tersedia'}</div>
                            </div>
                        </button>
                    `).join('');
                }

                function openClientDropdown() {
                    renderClientOptions(clientSearchInput.value);
                    clientDropdown.classList.remove('hidden');
                }

                function closeClientDropdown() {
                    clientDropdown.classList.add('hidden');
                }

                function selectClient(clientId) {
                    const client = clients.find((entry) => Number(entry.id) === Number(clientId));
                    if (!client) {
                        return;
                    }

                    clientIdField.value = client.id;
                    clientSearchInput.value = `${client.name} (${client.client_code})`;
                    renderClientMeta(client);
                    closeClientDropdown();
                    syncAllRowsForSelectedClient();
                    refreshDeliveryChannelState();
                }

                function updateDueDateFromMode() {
                    const invoiceDate = invoiceDateField.value ? new Date(`${invoiceDateField.value}T00:00:00`) : null;
                    const mode = dueDateModeField.value;

                    if (!invoiceDate) {
                        dueDateField.readOnly = mode !== 'custom';
                        return;
                    }

                    if (mode === 'custom') {
                        dueDateField.readOnly = false;
                        return;
                    }

                    const days = Number(mode);
                    const dueDate = new Date(invoiceDate);
                    dueDate.setDate(dueDate.getDate() + days);
                    dueDateField.value = dueDate.toISOString().slice(0, 10);
                    dueDateField.readOnly = true;
                }

                function getRowCatalog(row) {
                    const source = row.querySelector('.invoice-item-source')?.value || 'subscription';
                    const client = getSelectedClient();

                    if (source === 'subscription') {
                        return client?.subscriptions || [];
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
                    const subscriptionInput = row.querySelector('.invoice-item-subscription-id');
                    const packageInput = row.querySelector('.invoice-item-package-id');
                    const previousValue = source === 'subscription'
                        ? subscriptionInput?.value
                        : source === 'package'
                            ? packageInput?.value
                            : '';

                    if (!optionSelect) {
                        return;
                    }

                    optionSelect.innerHTML = '<option value="">Pilih item</option>';

                    if (source === 'manual') {
                        optionSelect.disabled = true;
                        optionHint.textContent = 'Deskripsi dan harga diisi manual.';
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
                        if (String(previousValue || '') === String(option.id)) {
                            optionElement.selected = true;
                        }
                        optionSelect.appendChild(optionElement);
                    });

                    optionHint.textContent = source === 'subscription'
                        ? 'Pilih layanan aktif pelanggan untuk mengisi deskripsi dan harga otomatis.'
                        : 'Pilih katalog layanan untuk mengisi item lebih cepat.';
                }

                function syncRowSelectionToInputs(row, preserveManualValues = true) {
                    const source = row.querySelector('.invoice-item-source')?.value || 'subscription';
                    const optionSelect = row.querySelector('.invoice-item-option');
                    const descriptionInput = row.querySelector('.invoice-item-description');
                    const amountInput = row.querySelector('.invoice-item-amount');
                    const subscriptionInput = row.querySelector('.invoice-item-subscription-id');
                    const packageInput = row.querySelector('.invoice-item-package-id');
                    const selectedValue = optionSelect?.value || '';
                    const selected = getRowCatalog(row).find((entry) => String(entry.id) === String(selectedValue));

                    subscriptionInput.value = source === 'subscription' && selected ? selected.id : '';
                    packageInput.value = source === 'package' && selected ? selected.id : '';

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

                function updateRowTotal(row) {
                    const qty = Number(row.querySelector('.invoice-item-qty')?.value || 0);
                    const amount = Number(row.querySelector('.invoice-item-amount')?.value || 0);
                    const total = qty * amount;
                    const totalNode = row.querySelector('.invoice-item-total');

                    if (totalNode) {
                        totalNode.textContent = formatCurrency(total);
                    }
                }

                function refreshInvoiceItemIndexes() {
                    invoiceItemsContainer.querySelectorAll('.invoice-item-row').forEach((row, index) => {
                        row.querySelectorAll('input, select[name], textarea[name]').forEach((field) => {
                            field.name = field.name.replace(/items\[\d+\]/, `items[${index}]`);
                        });
                    });
                }

                function updateInvoiceSummary() {
                    let subtotal = 0;

                    invoiceItemsContainer.querySelectorAll('.invoice-item-row').forEach((row) => {
                        const qty = Number(row.querySelector('.invoice-item-qty')?.value || 0);
                        const amount = Number(row.querySelector('.invoice-item-amount')?.value || 0);
                        const lineTotal = qty * amount;

                        subtotal += lineTotal;
                        updateRowTotal(row);
                    });

                    const tax = usesTaxField.checked ? subtotal * @json(\App\Models\SystemSetting::get('billing.ppn_rate', 11) / 100) : 0;
                    const discount = Number(discountAmountField.value || 0);
                    const grandTotal = Math.max(0, subtotal + tax - discount);

                    invoiceSubtotalValue.textContent = formatCurrency(subtotal);
                    invoiceTaxValue.textContent = formatCurrency(tax);
                    invoiceDiscountValue.textContent = formatCurrency(discount);
                    invoiceGrandTotalValue.textContent = formatCurrency(grandTotal);
                }

                function bindInvoiceItemRow(row) {
                    const qtyInput = row.querySelector('.invoice-item-qty');
                    const amountInput = row.querySelector('.invoice-item-amount');
                    const sourceField = row.querySelector('.invoice-item-source');
                    const optionField = row.querySelector('.invoice-item-option');
                    const removeButton = row.querySelector('.remove-invoice-item');

                    qtyInput?.addEventListener('input', updateInvoiceSummary);
                    amountInput?.addEventListener('input', updateInvoiceSummary);

                    sourceField?.addEventListener('change', function () {
                        syncRowOptionSelector(row);
                        syncRowSelectionToInputs(row, false);
                        updateInvoiceSummary();
                    });

                    optionField?.addEventListener('change', function () {
                        syncRowSelectionToInputs(row);
                        updateInvoiceSummary();
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

                        updateInvoiceSummary();
                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                    });
                }

                function createItemRow(index) {
                    const row = document.createElement('div');
                    row.className = 'invoice-item-row rounded-[1.35rem] border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-700 dark:bg-slate-900/30';
                    row.innerHTML = `
                        <input type="hidden" name="items[${index}][subscription_id]" value="" class="invoice-item-subscription-id">
                        <input type="hidden" name="items[${index}][package_id]" value="" class="invoice-item-package-id">
                        <div class="grid grid-cols-1 gap-3 lg:grid-cols-[170px_320px_90px_150px_150px_48px] lg:items-start">
                            <div>
                                <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Sumber</label>
                                <select name="items[${index}][source]" class="invoice-item-source w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                                    <option value="subscription" selected>Layanan Pelanggan</option>
                                    <option value="package">Katalog Layanan</option>
                                    <option value="manual">Item Manual</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Pilih Item</label>
                                <select class="invoice-item-option w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                                    <option value="">Pilih item</option>
                                </select>
                                <p class="invoice-item-option-hint mt-1 text-[11px] text-slate-500 dark:text-slate-400"></p>
                            </div>
                            <div>
                                <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Qty</label>
                                <input type="number" min="1" name="items[${index}][qty]" value="1" required class="invoice-item-qty w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                            </div>
                            <div>
                                <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Harga</label>
                                <input type="number" min="0" step="0.01" name="items[${index}][amount]" required class="invoice-item-amount w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white" placeholder="0">
                            </div>
                            <div>
                                <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Jumlah</label>
                                <div class="invoice-item-total rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-800 dark:border-slate-600 dark:bg-slate-800 dark:text-white">Rp 0</div>
                            </div>
                            <button type="button" class="remove-invoice-item inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 text-red-500 transition hover:bg-red-50 dark:border-slate-700 dark:hover:bg-red-900/20">
                                <i data-lucide="trash-2" class="h-4 w-4"></i>
                            </button>
                            <div class="lg:col-span-5">
                                <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Deskripsi</label>
                                <textarea name="items[${index}][description]" rows="2" required class="invoice-item-description w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white" placeholder="Contoh: Biaya instalasi / layanan tambahan"></textarea>
                            </div>
                        </div>
                    `;

                    return row;
                }

                function syncAllRowsForSelectedClient() {
                    invoiceItemsContainer.querySelectorAll('.invoice-item-row').forEach((row) => {
                        syncRowOptionSelector(row);
                        syncRowSelectionToInputs(row, false);
                    });
                    updateInvoiceSummary();
                }

                function refreshDeliveryChannelState() {
                    const client = getSelectedClient();
                    const hasEmail = Boolean(client?.email);
                    const hasWhatsapp = Boolean(client?.whatsapp);

                    sendEmailCheckbox.disabled = !hasEmail;
                    sendWhatsappCheckbox.disabled = !hasWhatsapp;

                    if (!hasEmail) {
                        sendEmailCheckbox.checked = false;
                        sendEmailFields.classList.add('hidden');
                    }

                    if (!hasWhatsapp) {
                        sendWhatsappCheckbox.checked = false;
                        sendWhatsappFields.classList.add('hidden');
                    }

                    sendEmailMeta.textContent = hasEmail
                        ? `Akan dikirim ke ${client.email}.`
                        : 'Kontak pelanggan belum memiliki email.';
                    sendWhatsappMeta.textContent = hasWhatsapp
                        ? `Akan dibuka ke ${client.whatsapp}.`
                        : 'Kontak pelanggan belum memiliki nomor WhatsApp.';
                }

                function buildEmailSubject() {
                    const client = getSelectedClient();
                    const clientName = client?.name || 'Pelanggan';
                    return `Invoice ${clientName} - ${invoiceDateField.value || ''}`;
                }

                function buildEmailBody() {
                    const client = getSelectedClient();
                    const clientName = client?.name || 'Pelanggan';
                    const total = invoiceGrandTotalValue.textContent || 'Rp 0';
                    const dueDate = dueDateField.value || '-';

                    return [
                        `Yth. ${clientName},`,
                        '',
                        'Berikut kami kirimkan invoice terbaru dari BMPnet.',
                        `Tanggal invoice: ${invoiceDateField.value || '-'}`,
                        `Jatuh tempo: ${dueDate}`,
                        `Total tagihan: ${total}`,
                        '',
                        'Mohon dapat ditindaklanjuti sesuai jatuh tempo yang tertera.',
                        '',
                        'Terima kasih.',
                        'Tim Billing BMPnet',
                    ].join('\n');
                }

                function buildWhatsappBody() {
                    const client = getSelectedClient();
                    const clientName = client?.name || 'Pelanggan';
                    const total = invoiceGrandTotalValue.textContent || 'Rp 0';
                    const dueDate = dueDateField.value || '-';

                    return [
                        `Halo ${clientName},`,
                        '',
                        'Berikut invoice terbaru dari BMPnet.',
                        `Tanggal invoice: ${invoiceDateField.value || '-'}`,
                        `Jatuh tempo: ${dueDate}`,
                        `Total tagihan: ${total}`,
                        '',
                        'Terima kasih.',
                        'Tim Billing BMPnet',
                    ].join('\n');
                }

                function seedDeliveryTemplates(force = false) {
                    if (force || !emailSubjectField.value) {
                        emailSubjectField.value = buildEmailSubject();
                    }
                    if (force || !emailBodyField.value) {
                        emailBodyField.value = buildEmailBody();
                    }
                    if (force || !whatsappBodyField.value) {
                        whatsappBodyField.value = buildWhatsappBody();
                    }
                }

                function setActiveSaveAction(action) {
                    activeSaveAction = action;
                    submitActionField.value = action;

                    const label = action === 'draft'
                        ? 'Simpan Draft'
                        : action === 'send'
                            ? 'Simpan & Kirim'
                            : 'Simpan & Konfirmasi';

                    savePrimaryButtonLabel.textContent = label;
                    saveActionPreview.textContent = label;
                }

                function submitFormForAction(action) {
                    setActiveSaveAction(action);

                    if (action === 'send') {
                        seedDeliveryTemplates(true);
                        refreshDeliveryChannelState();
                        openModal('invoiceSendModal');
                        return;
                    }

                    manualInvoiceForm.requestSubmit();
                }

                function toggleSignatureSections() {
                    const mode = document.querySelector('input[name="signature_mode"]:checked')?.value || 'none';

                    existingSignatureSection?.classList.toggle('hidden', mode !== 'existing');
                    uploadSignatureSection?.classList.toggle('hidden', mode !== 'upload');

                    if (mode === 'existing' && existingSignatureCount === 0) {
                        uploadSignatureSection?.classList.remove('hidden');
                    }

                    if (mode === 'existing') {
                        const checkedSignature = document.querySelector('input[name="existing_signature"]:checked');
                        const card = checkedSignature?.closest('[data-signature-select]');
                        if (card) {
                            signaturePreviewState.innerHTML = `
                                <div class="flex h-28 w-full max-w-xs items-center justify-center">
                                    <img src="${card.dataset.signatureUrl}" alt="Signature preview" class="max-h-full max-w-full object-contain">
                                </div>
                                <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">${card.dataset.signatureName}</div>
                            `;
                        }
                    } else if (mode === 'upload') {
                        signaturePreviewState.innerHTML = `
                            <div class="rounded-full bg-blue-100 p-3 text-blue-600 dark:bg-blue-500/15 dark:text-blue-300">
                                <i data-lucide="image-plus" class="h-6 w-6"></i>
                            </div>
                            <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">${signatureUploadInput?.files?.[0]?.name || 'Upload tanda tangan baru'}</div>
                        `;
                    } else {
                        signaturePreviewState.innerHTML = `
                            <div class="rounded-full bg-blue-100 p-3 text-blue-600 dark:bg-blue-500/15 dark:text-blue-300">
                                <i data-lucide="upload" class="h-6 w-6"></i>
                            </div>
                            <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">Pilih atau unggah tanda tangan</div>
                        `;
                    }

                    if (window.lucide) {
                        window.lucide.createIcons();
                    }
                }

                clientSearchInput?.addEventListener('focus', openClientDropdown);
                clientSearchInput?.addEventListener('input', function () {
                    openClientDropdown();
                    renderClientOptions(clientSearchInput.value);
                });
                clientDropdownToggle?.addEventListener('click', function () {
                    if (clientDropdown.classList.contains('hidden')) {
                        openClientDropdown();
                    } else {
                        closeClientDropdown();
                    }
                });

                document.addEventListener('click', function (event) {
                    if (!document.getElementById('clientCombobox').contains(event.target)) {
                        closeClientDropdown();
                    }

                    if (!document.getElementById('saveActionsDropdown').contains(event.target)) {
                        saveDropdownMenu.classList.add('hidden');
                    }
                });

                clientDropdownOptions.addEventListener('click', function (event) {
                    const button = event.target.closest('[data-client-option]');
                    if (!button) {
                        return;
                    }

                    selectClient(button.dataset.clientOption);
                });

                invoiceDateField?.addEventListener('change', function () {
                    updateDueDateFromMode();
                    seedDeliveryTemplates(true);
                });

                dueDateModeField?.addEventListener('change', updateDueDateFromMode);
                dueDateField?.addEventListener('change', function () {
                    seedDeliveryTemplates(true);
                });
                usesTaxField?.addEventListener('change', function () {
                    updateInvoiceSummary();
                    seedDeliveryTemplates(true);
                });
                discountAmountField?.addEventListener('input', function () {
                    updateInvoiceSummary();
                    seedDeliveryTemplates(true);
                });

                addInvoiceItemButton?.addEventListener('click', function () {
                    const index = invoiceItemsContainer.querySelectorAll('.invoice-item-row').length;
                    const row = createItemRow(index);
                    invoiceItemsContainer.appendChild(row);
                    bindInvoiceItemRow(row);
                    syncRowOptionSelector(row);
                    syncRowSelectionToInputs(row, false);
                    updateInvoiceSummary();
                    if (window.lucide) {
                        window.lucide.createIcons();
                    }
                });

                invoiceItemsContainer.querySelectorAll('.invoice-item-row').forEach((row) => {
                    bindInvoiceItemRow(row);
                    syncRowOptionSelector(row);
                    syncRowSelectionToInputs(row, true);
                });

                saveDropdownToggle?.addEventListener('click', function () {
                    saveDropdownMenu.classList.toggle('hidden');
                });

                savePrimaryButton?.addEventListener('click', function () {
                    submitFormForAction(activeSaveAction);
                });

                saveActionButtons.forEach((button) => {
                    button.addEventListener('click', function () {
                        saveDropdownMenu.classList.add('hidden');
                        submitFormForAction(button.dataset.saveAction);
                    });
                });

                sendEmailCheckbox?.addEventListener('change', function () {
                    sendEmailFields.classList.toggle('hidden', !sendEmailCheckbox.checked);
                    if (sendEmailCheckbox.checked && !emailSubjectField.value) {
                        seedDeliveryTemplates(true);
                    }
                });

                sendWhatsappCheckbox?.addEventListener('change', function () {
                    sendWhatsappFields.classList.toggle('hidden', !sendWhatsappCheckbox.checked);
                    if (sendWhatsappCheckbox.checked && !whatsappBodyField.value) {
                        seedDeliveryTemplates(true);
                    }
                });

                submitSendAction?.addEventListener('click', function () {
                    setActiveSaveAction('send');
                    closeModal('invoiceSendModal');
                    manualInvoiceForm.requestSubmit();
                });

                document.querySelectorAll('input[name="signature_mode"]').forEach((radio) => {
                    radio.addEventListener('change', toggleSignatureSections);
                });

                manualInvoiceForm.addEventListener('submit', function () {
                    setActiveSaveAction(submitActionField.value || activeSaveAction);
                });

                if (!clientIdField.value && clients.length > 0 && oldItems.length === 1 && !oldItems[0].description) {
                    renderClientMeta(null);
                } else if (clientIdField.value) {
                    renderClientMeta(getSelectedClient());
                }

                updateDueDateFromMode();
                refreshInvoiceItemIndexes();
                syncAllRowsForSelectedClient();
                updateInvoiceSummary();
                refreshDeliveryChannelState();
                seedDeliveryTemplates(false);
                setActiveSaveAction(activeSaveAction);
                toggleSignatureSections();

                if (@json(old('submit_action') === 'send')) {
                    sendEmailFields.classList.toggle('hidden', !sendEmailCheckbox.checked);
                    sendWhatsappFields.classList.toggle('hidden', !sendWhatsappCheckbox.checked);
                    openModal('invoiceSendModal');
                }

                if (window.lucide) {
                    window.lucide.createIcons();
                }
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const signaturePreviewState = document.getElementById('signaturePreviewState');
                const clearSignatureButton = document.getElementById('clearSignatureButton');
                const existingSignatureSection = document.getElementById('existingSignatureSection');
                const uploadSignatureSection = document.getElementById('uploadSignatureSection');
                const uploadSignatureInput = document.querySelector('#uploadSignatureSection input[name="signature_upload"]');
                const signatureModeInputs = document.querySelectorAll('input[name="signature_mode"]');
                const existingSignatureInputs = document.querySelectorAll('input[name="existing_signature"]');
                const signatureCards = document.querySelectorAll('[data-signature-select]');

                if (!signaturePreviewState) {
                    return;
                }

                existingSignatureSection?.classList.remove('hidden');
                uploadSignatureSection?.classList.remove('hidden');

                function setSignatureMode(mode) {
                    signatureModeInputs.forEach((input) => {
                        input.checked = input.value === mode;
                    });
                }

                function updateSignaturePreview(mode = null, overrideFileName = null) {
                    const activeMode = mode || document.querySelector('input[name="signature_mode"]:checked')?.value || 'none';

                    if (activeMode === 'existing') {
                        const checkedSignature = document.querySelector('input[name="existing_signature"]:checked');
                        const card = checkedSignature?.closest('[data-signature-select]');

                        if (card) {
                            signaturePreviewState.innerHTML = `
                                <div class="flex h-28 w-full max-w-xs items-center justify-center">
                                    <img src="${card.dataset.signatureUrl}" alt="Signature preview" class="max-h-full max-w-full object-contain">
                                </div>
                                <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">${card.dataset.signatureName}</div>
                            `;
                        }
                    } else if (activeMode === 'upload') {
                        const fileName = overrideFileName || uploadSignatureInput?.files?.[0]?.name || 'Upload tanda tangan baru';
                        signaturePreviewState.innerHTML = `
                            <div class="rounded-full bg-blue-100 p-3 text-blue-600 dark:bg-blue-500/15 dark:text-blue-300">
                                <i data-lucide="image-plus" class="h-6 w-6"></i>
                            </div>
                            <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">${fileName}</div>
                        `;
                    } else {
                        signaturePreviewState.innerHTML = `
                            <div class="rounded-full bg-blue-100 p-3 text-blue-600 dark:bg-blue-500/15 dark:text-blue-300">
                                <i data-lucide="upload" class="h-6 w-6"></i>
                            </div>
                            <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">Pilih atau unggah tanda tangan</div>
                        `;
                    }

                    if (window.lucide) {
                        window.lucide.createIcons();
                    }
                }

                signatureCards.forEach((button) => {
                    button.addEventListener('click', function () {
                        const radio = button.querySelector('input[name="existing_signature"]');
                        if (radio) {
                            radio.checked = true;
                        }

                        setSignatureMode('existing');
                        updateSignaturePreview('existing');
                        closeModal('signaturePickerModal');
                    });
                });

                clearSignatureButton?.addEventListener('click', function () {
                    existingSignatureInputs.forEach((input) => {
                        input.checked = false;
                    });

                    if (uploadSignatureInput) {
                        uploadSignatureInput.value = '';
                    }

                    setSignatureMode('none');
                    updateSignaturePreview('none');
                    closeModal('signaturePickerModal');
                });

                uploadSignatureInput?.addEventListener('change', function () {
                    if (uploadSignatureInput.files?.length) {
                        existingSignatureInputs.forEach((input) => {
                            input.checked = false;
                        });

                        setSignatureMode('upload');
                        updateSignaturePreview('upload', uploadSignatureInput.files[0].name);
                        closeModal('signaturePickerModal');
                    }
                });

                updateSignaturePreview();
            });
        </script>
    @endpush
</x-app-layout>
