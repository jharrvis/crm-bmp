<x-app-layout>
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-6 py-6 md:px-8 border-b border-slate-200 dark:border-slate-700">
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Tambah Pembayaran</h1>
                @if($invoice)
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Untuk invoice: <a href="{{ route('invoices.show', $invoice) }}" class="text-blue-600 hover:underline font-semibold">{{ $invoice->invoice_number }}</a> ({{ $invoice->client->name }})
                    </p>
                @endif
            </div>

            <form action="{{ route('payments.store') }}" method="POST" enctype="multipart/form-data" class="p-6 md:p-8 space-y-6">
                @csrf
                
                @if($invoice)
                    <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                @else
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">Pilih Invoice <span class="text-red-500">*</span></label>
                        <!-- In real app, this should be a searchable select2/choices.js -->
                        <input type="number" name="invoice_id" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-4 py-2.5" placeholder="ID Invoice" required>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">Tanggal Pembayaran <span class="text-red-500">*</span></label>
                        <input type="date" name="payment_date" value="{{ old('payment_date', now()->format('Y-m-d')) }}" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-4 py-2.5" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">Metode <span class="text-red-500">*</span></label>
                        <select name="payment_method" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-4 py-2.5" required>
                            <option value="transfer">Transfer Bank</option>
                            <option value="cash">Tunai (Cash)</option>
                            <option value="qris">QRIS</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">Jumlah Pembayaran (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" value="{{ old('amount', $invoice ? $invoice->total_amount : '') }}" step="0.01" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-4 py-2.5 text-lg font-bold" required>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">Nomor Referensi (Opsional)</label>
                    <input type="text" name="reference_number" value="{{ old('reference_number') }}" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-4 py-2.5" placeholder="Contoh: INV/2026/01/123 atau ref bank">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">Bukti Transfer (Opsional)</label>
                    <input type="file" name="proof_file" accept=".jpg,.jpeg,.png,.pdf" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="mt-1 text-xs text-slate-400">Max 2MB. JPG, PNG, PDF.</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">Catatan Internal (Opsional)</label>
                    <textarea name="notes" rows="3" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-4 py-2.5">{{ old('notes') }}</textarea>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-bold bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Simpan Pembayaran
                    </button>
                    @if($invoice)
                        <a href="{{ route('invoices.show', $invoice) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors">
                            Batal
                        </a>
                    @else
                        <a href="{{ route('payments.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors">
                            Batal
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
