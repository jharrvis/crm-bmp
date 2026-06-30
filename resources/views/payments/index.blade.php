<x-app-layout>
    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-6 py-6 md:px-8 border-b border-slate-200 dark:border-slate-700">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Daftar Pembayaran</h1>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Kelola histori pembayaran dan verifikasi bukti transfer.
                        </p>
                    </div>
                </div>
            </div>

            <form method="GET" class="px-6 py-5 md:px-8 flex flex-col md:flex-row gap-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                <div class="flex-1">
                    <label class="sr-only">Filter Status</label>
                    <select name="status" class="w-full md:w-64 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 px-4 py-2 text-sm text-slate-900 dark:text-white outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending (Menunggu Verifikasi)</option>
                        <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified (Lunas)</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected (Ditolak)</option>
                    </select>
                </div>
            </form>

            @if(session('success'))
                <div class="mx-6 mt-4 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 dark:bg-green-900/20 dark:border-green-800 dark:text-green-300">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mx-6 mt-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 dark:bg-red-900/20 dark:border-red-800 dark:text-red-300">
                    {{ session('error') }}
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                            <th class="px-6 py-4 font-bold text-slate-500 dark:text-slate-400">Tanggal</th>
                            <th class="px-6 py-4 font-bold text-slate-500 dark:text-slate-400">Invoice</th>
                            <th class="px-6 py-4 font-bold text-slate-500 dark:text-slate-400">Client</th>
                            <th class="px-6 py-4 font-bold text-slate-500 dark:text-slate-400">Jumlah</th>
                            <th class="px-6 py-4 font-bold text-slate-500 dark:text-slate-400">Metode</th>
                            <th class="px-6 py-4 font-bold text-slate-500 dark:text-slate-400">Status</th>
                            <th class="px-6 py-4 font-bold text-slate-500 dark:text-slate-400 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse($payments as $payment)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="px-6 py-4 text-slate-800 dark:text-slate-200">
                                    {{ $payment->payment_date->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 text-slate-800 dark:text-slate-200">
                                    <a href="{{ route('invoices.show', $payment->invoice_id) }}" class="text-blue-600 hover:underline">
                                        {{ $payment->invoice->invoice_number }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-slate-800 dark:text-slate-200">
                                    {{ $payment->invoice->client->name }}
                                </td>
                                <td class="px-6 py-4 text-slate-800 dark:text-slate-200 font-bold">
                                    Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-slate-500 dark:text-slate-400 capitalize">
                                    {{ $payment->payment_method }}
                                    @if($payment->reference_number)
                                        <div class="text-xs">Ref: {{ $payment->reference_number }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($payment->status === 'verified')
                                        <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-800 dark:bg-green-900/30 dark:text-green-400">Verified</span>
                                        <div class="text-xs text-slate-500 mt-1">By {{ $payment->verifiedBy->name ?? 'System' }}</div>
                                    @elseif($payment->status === 'rejected')
                                        <span class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-800 dark:bg-red-900/30 dark:text-red-400">Rejected</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-orange-100 px-2.5 py-1 text-xs font-semibold text-orange-800 dark:bg-orange-900/30 dark:text-orange-400">Pending</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right flex justify-end gap-2">
                                    @if($payment->proof_path)
                                        <a href="{{ Storage::disk('public')->url($payment->proof_path) }}" target="_blank" class="text-slate-500 hover:text-slate-700 dark:hover:text-slate-300" title="Lihat Bukti Transfer">
                                            <i data-lucide="image" class="w-5 h-5"></i>
                                        </a>
                                    @endif
                                    
                                    @can('payments.verify')
                                        @if($payment->status === 'pending')
                                            <form action="{{ route('payments.verify', $payment) }}" method="POST" onsubmit="return confirm('Verifikasi pembayaran ini?');">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-800 dark:hover:text-green-400" title="Verifikasi">
                                                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                                                </button>
                                            </form>
                                            
                                            <!-- Simple inline reject form for now -->
                                            <form action="{{ route('payments.reject', $payment) }}" method="POST" onsubmit="const r = prompt('Alasan penolakan?'); if(r) { this.rejected_reason.value = r; return true; } return false;">
                                                @csrf
                                                <input type="hidden" name="rejected_reason" value="">
                                                <button type="submit" class="text-red-600 hover:text-red-800 dark:hover:text-red-400" title="Tolak">
                                                    <i data-lucide="x-circle" class="w-5 h-5"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400">
                                    Tidak ada data pembayaran yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payments->hasPages())
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
