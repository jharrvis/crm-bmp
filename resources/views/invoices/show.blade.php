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
                print-color-adjust: exact;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body class="bg-slate-100 font-sans text-slate-900">
    @php
        $branch = $invoice->resolveBranch();
        $billingSummary = $invoice->calculateBillingSummary();
    @endphp

    <div id="invoice" class="mx-auto my-8 max-w-5xl rounded-3xl bg-white px-8 py-10 shadow-xl md:px-12">
        <div class="flex items-start justify-between gap-8">
            <div class="pt-1">
                <div class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">The Internet Service Provider</div>
                <div class="mt-2 flex items-end gap-1 leading-none">
                    <span class="text-6xl font-black italic tracking-tight text-blue-700">bmp</span>
                    <span class="text-6xl font-black italic tracking-tight text-red-600">net</span>
                </div>
            </div>
            <div class="text-right">
                <div class="text-3xl italic text-blue-900" style="font-family: 'Brush Script MT', cursive;">get Connected!</div>
                <div class="mt-8 text-6xl font-black tracking-tight text-slate-900">Invoice</div>
            </div>
        </div>

        <div class="mt-12 grid grid-cols-1 gap-10 md:grid-cols-[1.15fr_0.85fr]">
            <div>
                <div class="text-3xl font-extrabold text-slate-900">{{ $branch?->name ?? 'BMPNET' }}</div>
                <div class="mt-3 space-y-1 text-2xl font-semibold leading-relaxed text-slate-800">
                    @if($branch?->address)
                        @foreach(preg_split("/\r\n|\n|\r/", $branch->address) as $addressLine)
                            @if(trim($addressLine) !== '')
                                <div>{{ $addressLine }}</div>
                            @endif
                        @endforeach
                    @endif
                    @if($branch?->phone)
                        <div>{{ $branch->phone }}</div>
                    @endif
                </div>
            </div>

            <div class="space-y-7">
                <div class="grid grid-cols-[140px_1fr] gap-x-4 gap-y-2 text-lg">
                    <div class="font-bold text-slate-800">Inv No</div>
                    <div class="text-right font-semibold text-slate-700">{{ $invoice->invoice_number }}</div>

                    <div class="font-bold text-slate-800">Date</div>
                    <div class="text-right font-semibold text-slate-700">{{ $invoice->invoice_date->format('d-M-Y') }}</div>

                    <div class="font-bold text-slate-800">Due Date</div>
                    <div class="text-right font-semibold text-slate-700">{{ $invoice->due_date->format('d-M-Y') }}</div>

                    <div class="font-bold text-slate-800">PO. No</div>
                    <div class="text-right font-semibold text-slate-500">-</div>
                </div>

                <div>
                    <div class="bg-slate-100 px-3 py-1 text-base font-bold text-slate-800">Bill To :</div>
                    <div class="mt-2 text-lg leading-relaxed text-slate-800">
                        <div class="font-bold">{{ $invoice->client->name }}</div>
                        @if($invoice->client->address)
                            <div>{{ $invoice->client->address }}</div>
                        @endif
                        @if($invoice->client->city)
                            <div>{{ $invoice->client->city }}</div>
                        @endif
                        <div class="font-medium">{{ $invoice->client->client_code }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-12 overflow-hidden rounded-2xl border border-slate-200">
            <table class="w-full border-collapse">
                <thead class="bg-slate-50">
                    <tr class="text-left text-sm font-bold uppercase tracking-[0.18em] text-slate-600">
                        <th class="px-5 py-4 w-16 border-b border-slate-200">No.</th>
                        <th class="px-5 py-4 border-b border-slate-200">Description</th>
                        <th class="px-5 py-4 w-24 border-b border-slate-200 text-center">QYT</th>
                        <th class="px-5 py-4 w-48 border-b border-slate-200 text-right">Price</th>
                        <th class="px-5 py-4 w-48 border-b border-slate-200 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="text-base text-slate-800">
                    @foreach($invoice->items as $index => $item)
                        <tr>
                            <td class="px-5 py-4 align-top border-b border-slate-100">{{ $index + 1 }}</td>
                            <td class="px-5 py-4 align-top border-b border-slate-100">{{ $item->description }}</td>
                            <td class="px-5 py-4 align-top border-b border-slate-100 text-center">{{ $item->qty }}</td>
                            <td class="px-5 py-4 align-top border-b border-slate-100 text-right">{{ number_format($item->billing_base_amount, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 align-top border-b border-slate-100 text-right">{{ number_format($item->billing_line_total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-8 md:grid-cols-[1fr_380px]">
            <div class="pt-3">
                <div class="text-xl font-bold text-slate-800">Amount in words / Terbilang</div>
                <div class="mt-3 max-w-2xl text-2xl italic leading-relaxed text-slate-900">
                    {{ $invoice->amount_in_words }}
                </div>
                <div class="mt-8 text-sm text-slate-500">
                    {{ $invoice->notes ?? 'Terima kasih telah berlangganan layanan kami.' }}
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200">
                <div class="grid grid-cols-[1fr_170px] text-lg">
                    <div class="border-b border-slate-200 px-5 py-3 font-bold text-slate-800">Harga Jual</div>
                    <div class="border-b border-slate-200 px-5 py-3 text-right font-semibold text-slate-800">
                        {{ number_format($billingSummary['subtotal'], 0, ',', '.') }}
                    </div>

                    @if($billingSummary['ppn_amount'] > 0)
                        <div class="border-b border-slate-200 px-5 py-3 font-bold text-slate-800">PPN 11%</div>
                        <div class="border-b border-slate-200 px-5 py-3 text-right font-semibold text-slate-800">
                            {{ number_format($billingSummary['ppn_amount'], 0, ',', '.') }}
                        </div>
                    @endif

                    @if($billingSummary['pph23_amount'] > 0)
                        <div class="border-b border-slate-200 px-5 py-3 font-bold text-slate-800">PPh23 2%</div>
                        <div class="border-b border-slate-200 px-5 py-3 text-right font-semibold text-slate-800">
                            {{ number_format($billingSummary['pph23_amount'], 0, ',', '.') }}
                        </div>
                    @endif

                    <div class="px-5 py-4 text-2xl font-extrabold text-slate-900">Total Tagihan</div>
                    <div class="px-5 py-4 text-right text-2xl font-extrabold text-slate-900">
                        {{ number_format($billingSummary['total_amount'], 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="no-print fixed bottom-5 right-5">
        <button onclick="window.print()"
            class="flex items-center gap-2 rounded-full bg-blue-700 px-5 py-3 font-bold text-white shadow-lg transition-colors hover:bg-blue-800">
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
