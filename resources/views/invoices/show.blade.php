<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body {
                -webkit-print-color-adjust: exact;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body class="bg-gray-100 font-sans text-slate-800">

    <div class="max-w-3xl mx-auto bg-white shadow-lg my-10 p-10 rounded-xl" id="invoice">

        <!-- Header -->
        <div class="flex justify-between items-start mb-10 border-b pb-8">
            <div>
                <h1 class="text-4xl font-bold text-slate-800 mb-2">INVOICE</h1>
                <p class="text-slate-500 text-sm">No. Inv: <span
                        class="font-mono font-bold text-slate-800">{{ $invoice->invoice_number }}</span></p>
                <p class="text-slate-500 text-sm">Tanggal: {{ $invoice->invoice_date->format('d F Y') }}</p>
                <p class="text-slate-500 text-sm">Jatuh Tempo: <span
                        class="text-red-600 font-bold">{{ $invoice->due_date->format('d F Y') }}</span></p>

                <div class="mt-4">
                    @if($invoice->status == 'paid')
                        <span
                            class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-bold border border-green-200 uppercase tracking-wide">LUNAS
                            / PAID</span>
                    @elseif($invoice->status == 'unpaid')
                        <span
                            class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-bold border border-red-200 uppercase tracking-wide">BELUM
                            LUNAS / UNPAID</span>
                    @endif
                </div>
            </div>
            <div class="text-right">
                <div class="font-bold text-xl text-blue-600 mb-1">BMPNET</div>
                <div class="text-sm text-slate-500">
                    Jl. Jenderal Sudirman No. 123<br>
                    Salatiga, Jawa Tengah<br>
                    billing@bmpnet.id | 0298-123456
                </div>
            </div>
        </div>

        <!-- Bill To -->
        <div class="mb-10">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Ditagihkan Kepada:</h3>
            <div class="font-bold text-lg">{{ $invoice->client->name }}</div>
            <div class="text-slate-600 text-sm max-w-xs">
                {{ $invoice->client->address }}<br>
                {{ $invoice->client->city }}
            </div>
            <div class="text-slate-500 text-sm mt-1">{{ $invoice->client->client_code }}</div>
        </div>

        <!-- Table -->
        <div class="mb-10">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-xs font-bold text-slate-500 uppercase border-b-2 border-slate-200">
                        <th class="py-3">Deskripsi</th>
                        <th class="py-3 text-center">Qty</th>
                        <th class="py-3 text-right">Harga Satuan</th>
                        <th class="py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @foreach($invoice->items as $item)
                        <tr class="border-b border-slate-100">
                            <td class="py-4 font-medium">{{ $item->description }}</td>
                            <td class="py-4 text-center">{{ $item->qty }}</td>
                            <td class="py-4 text-right">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                            <td class="py-4 text-right font-bold">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="pt-6 text-right font-bold text-slate-600">Grand Total</td>
                        <td class="pt-6 text-right font-bold text-xl text-blue-600">Rp
                            {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Footer / Notes -->
        <div class="border-t pt-8 text-slate-500 text-sm">
            <p class="font-bold mb-2 text-slate-700">Catatan:</p>
            <p class="italic mb-4">{{ $invoice->notes ?? 'Terima kasih telah berlangganan layanan kami.' }}</p>

            <p class="text-xs">
                Harap lakukan pembayaran sebelum tanggal jatuh tempo. Transfer ke BCA 1234567890 a.n BMPNET.
            </p>
        </div>

    </div>

    <div class="fixed bottom-5 right-5 no-print flex gap-2">
        <button onclick="window.print()"
            class="bg-blue-600 text-white px-5 py-3 rounded-full shadow-lg font-bold hover:bg-blue-700 transition-colors flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            Cetak PDF
        </button>
    </div>

</body>

</html>