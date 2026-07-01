<x-app-layout>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-6 py-6 md:px-8 border-b border-slate-200 dark:border-slate-700">
                <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5">
                    <div>
                        <h3 class="text-2xl font-bold text-slate-800 dark:text-white">Laporan Keuangan</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Ringkasan pendapatan, tagihan, dan piutang periode {{ \Carbon\Carbon::createFromFormat('Y-m', $period)->translatedFormat('F Y') }}
                        </p>
                    </div>
                    <form method="GET" class="flex items-center gap-3">
                        <label class="text-sm font-medium text-slate-600 dark:text-slate-400">Periode</label>
                        <input type="month" name="period" value="{{ $period }}"
                            class="rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-4 py-2.5">
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-colors">
                            Tampilkan
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Pendapatan Terverifikasi</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">
                            Rp {{ number_format($verifiedPaymentsTotal, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="p-3 rounded-xl bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400">
                        <i data-lucide="check-circle" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Tagihan Diterbitkan</p>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">
                            Rp {{ number_format($invoicesIssuedTotal, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">
                        <i data-lucide="receipt" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Tagihan Lunas</p>
                        <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">
                            Rp {{ number_format($paidInvoicesTotal, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400">
                        <i data-lucide="badge-check" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Piutang</p>
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">
                            Rp {{ number_format($outstandingTotal, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="p-3 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400">
                        <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Aging Report --}}
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-6 py-5 md:px-8 border-b border-slate-200 dark:border-slate-700">
                <h4 class="text-lg font-bold text-slate-800 dark:text-white">Piutang Menurut Umur</h4>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Status overdue berdasarkan jatuh tempo</p>
            </div>
            <div class="px-6 py-6 md:px-8">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="p-4 rounded-xl bg-yellow-50 dark:bg-yellow-900/10 border border-yellow-200 dark:border-yellow-800">
                        <p class="text-xs font-semibold text-yellow-700 dark:text-yellow-400 uppercase tracking-wider">0 - 30 Hari</p>
                        <p class="text-xl font-bold text-yellow-800 dark:text-yellow-300 mt-1">
                            Rp {{ number_format($aging0to30, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="p-4 rounded-xl bg-orange-50 dark:bg-orange-900/10 border border-orange-200 dark:border-orange-800">
                        <p class="text-xs font-semibold text-orange-700 dark:text-orange-400 uppercase tracking-wider">31 - 60 Hari</p>
                        <p class="text-xl font-bold text-orange-800 dark:text-orange-300 mt-1">
                            Rp {{ number_format($aging31to60, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="p-4 rounded-xl bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800">
                        <p class="text-xs font-semibold text-red-700 dark:text-red-400 uppercase tracking-wider">61 - 90 Hari</p>
                        <p class="text-xl font-bold text-red-800 dark:text-red-300 mt-1">
                            Rp {{ number_format($aging61to90, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-900/10 border border-rose-200 dark:border-rose-800">
                        <p class="text-xs font-semibold text-rose-700 dark:text-rose-400 uppercase tracking-wider">> 90 Hari</p>
                        <p class="text-xl font-bold text-rose-800 dark:text-rose-300 mt-1">
                            Rp {{ number_format($agingOver90, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Payments --}}
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-6 py-5 md:px-8 border-b border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-lg font-bold text-slate-800 dark:text-white">Pembayaran Terbaru</h4>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">10 pembayaran terakhir periode ini</p>
                    </div>
                    @can('payments.view')
                    <a href="{{ route('payments.index') }}"
                        class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">
                        Lihat Semua
                    </a>
                    @endcan
                </div>
            </div>

            @if($recentPayments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-700">
                                <th class="text-left px-6 py-4 font-semibold text-slate-600 dark:text-slate-400">Tanggal</th>
                                <th class="text-left px-6 py-4 font-semibold text-slate-600 dark:text-slate-400">Invoice</th>
                                <th class="text-left px-6 py-4 font-semibold text-slate-600 dark:text-slate-400">Pelanggan</th>
                                <th class="text-left px-6 py-4 font-semibold text-slate-600 dark:text-slate-400">Metode</th>
                                <th class="text-right px-6 py-4 font-semibold text-slate-600 dark:text-slate-400">Jumlah</th>
                                <th class="text-center px-6 py-4 font-semibold text-slate-600 dark:text-slate-400">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentPayments as $payment)
                            <tr class="border-b border-slate-50 dark:border-slate-700/50 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-6 py-4 text-slate-700 dark:text-slate-300 whitespace-nowrap">
                                    {{ $payment->payment_date->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('invoices.show', $payment->invoice_id) }}"
                                        class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
                                        {{ $payment->invoice->invoice_number ?? 'N/A' }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-slate-700 dark:text-slate-300">
                                    {{ $payment->invoice->client->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-slate-700 dark:text-slate-300">
                                    {{ ucfirst($payment->payment_method) }}
                                </td>
                                <td class="px-6 py-4 text-right font-medium text-slate-800 dark:text-slate-200">
                                    Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($payment->status === 'verified')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                            Terverifikasi
                                        </span>
                                    @elseif($payment->status === 'pending')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                                            Pending
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                            Ditolak
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-10 text-center">
                    <i data-lucide="credit-card" class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600 mb-3"></i>
                    <p class="text-slate-500 dark:text-slate-400 text-sm">Belum ada pembayaran tercatat periode ini.</p>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
    </script>
    @endpush
</x-app-layout>
