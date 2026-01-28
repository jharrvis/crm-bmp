<x-app-layout>
    <div class="space-y-6">
        <div
            class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6 md:p-8">

            <!-- Toolbar -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <div>
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white">Daftar Tagihan</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Kelola invoice dan status pembayaran.</p>
                </div>
                <!-- Action Buttons -->
                <div class="flex gap-3">
                    <button onclick="generateInvoices()" id="btnGenerate"
                        class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-indigo-200 dark:shadow-none transition-all">
                        <i data-lucide="zap" class="w-5 h-5"></i>
                        <span>Generate Bulanan</span>
                    </button>
                    <!-- Manual Invoice (Later) -->
                    <!-- <button onclick="window.openModal()" ...> -->
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto no-scrollbar">
                <table id="dataTable" class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-700">
                            <th class="p-4 pl-6">No. Invoice</th>
                            <th class="p-4">Pelanggan</th>
                            <th class="p-4">Tanggal / Jatuh Tempo</th>
                            <th class="p-4">Total</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 pr-6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach($invoices as $invoice)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="p-4 pl-6 font-mono font-bold text-slate-600 dark:text-slate-300">
                                    {{ $invoice->invoice_number }}
                                </td>
                                <td class="p-4">
                                    <div class="font-bold text-slate-800 dark:text-white">{{ $invoice->client->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $invoice->client->client_code }}</div>
                                </td>
                                <td class="p-4">
                                    <div class="text-xs font-bold text-slate-500 uppercase">Inv:
                                        {{ $invoice->invoice_date->format('d/m/Y') }}</div>
                                    <div class="text-xs text-red-500">Due: {{ $invoice->due_date->format('d/m/Y') }}</div>
                                </td>
                                <td class="p-4 font-bold text-slate-800 dark:text-white">
                                    Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                                </td>
                                <td class="p-4">
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
                                    @endphp
                                    <span
                                        class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $statusClasses[$invoice->status] ?? '' }}">
                                        {{ $labels[$invoice->status] ?? $invoice->status }}
                                    </span>
                                </td>
                                <td class="p-4 pr-6 text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="{{ route('invoices.show', $invoice) }}" target="_blank"
                                            class="p-2 hover:bg-blue-50 dark:hover:bg-blue-900/30 text-blue-600 rounded-lg transition-colors"
                                            title="Cetak / Lihat">
                                            <i data-lucide="printer" class="w-4 h-4"></i>
                                        </a>

                                        @if($invoice->status == 'unpaid')
                                            <button onclick="markAsPaid({{ $invoice->id }}, '{{ $invoice->invoice_number }}')"
                                                class="p-2 hover:bg-green-50 dark:hover:bg-green-900/30 text-green-600 rounded-lg transition-colors"
                                                title="Tandai Lunas">
                                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                            </button>
                                        @endif

                                        <button onclick="deleteInvoice({{ $invoice->id }})"
                                            class="p-2 hover:bg-red-50 dark:hover:bg-red-900/30 text-red-600 rounded-lg transition-colors"
                                            title="Hapus">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
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
                $('#dataTable').DataTable({
                    language: {
                        search: "",
                        searchPlaceholder: "Cari invoice...",
                        lengthMenu: "Tampilkan _MENU_",
                        info: "_START_ - _END_ dari _TOTAL_",
                        paginate: { first: "«", last: "»", next: "›", previous: "‹" }
                    },
                    drawCallback: function () { lucide.createIcons(); }
                });
            });

            function generateInvoices() {
                const btn = document.getElementById('btnGenerate');
                if (!confirm('Generate invoice otomatis untuk pelanggan aktif bulan ini?')) return;

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
                            alert(res.message);
                            window.location.reload();
                        } else {
                            alert('Gagal: ' + res.message);
                            btn.disabled = false;
                            btn.innerHTML = '<i data-lucide="zap"></i> Generate Bulanan';
                            lucide.createIcons();
                        }
                    })
                    .catch(err => {
                        alert('Terjadi kesalahan server.');
                        console.error(err);
                        btn.disabled = false;
                    });
            }

            function markAsPaid(id, number) {
                if (!confirm(`Tandai invoice ${number} sebagai LUNAS?`)) return;

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

            function deleteInvoice(id) {
                if (!confirm('Hapus invoice ini? Data tidak bisa dikembalikan.')) return;

                fetch(`/invoices/${id}`, {
                    method: 'DELETE',
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
                            alert('Gagal menghapus.');
                        }
                    });
            }
        </script>
    @endpush
</x-app-layout>