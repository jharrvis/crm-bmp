<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        #invoice {
            width: 100%;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm;
            }

            html,
            body {
                background: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                margin: 0;
                padding: 0;
                font-size: 11px;
            }

            .no-print {
                display: none;
            }

            #invoice {
                max-width: none !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 20px 22px !important;
                border-radius: 0 !important;
                box-shadow: none !important;
            }

            #invoice .print-header {
                gap: 18px !important;
            }

            #invoice .print-logo-box {
                width: 145px !important;
                height: 56px !important;
                font-size: 11px !important;
            }

            #invoice .print-title {
                margin-top: 14px !important;
                font-size: 46px !important;
                line-height: 1 !important;
            }

            #invoice .print-main-grid {
                margin-top: 24px !important;
                grid-template-columns: minmax(0, 1.08fr) minmax(0, 0.92fr) !important;
                gap: 22px !important;
            }

            #invoice .print-branch-name {
                font-size: 18px !important;
            }

            #invoice .print-branch-address {
                margin-top: 10px !important;
                font-size: 13px !important;
                line-height: 1.45 !important;
            }

            #invoice .print-meta-grid,
            #invoice .print-billto-body {
                font-size: 12px !important;
            }

            #invoice .print-section-gap {
                margin-top: 28px !important;
            }

            #invoice .print-table-head {
                font-size: 10px !important;
            }

            #invoice .print-table-body {
                font-size: 11px !important;
            }

            #invoice .print-table-cell {
                padding: 10px 12px !important;
            }

            #invoice .print-bottom-grid {
                grid-template-columns: minmax(0, 1fr) 290px !important;
                gap: 18px !important;
            }

            #invoice .print-terbilang-title,
            #invoice .print-summary-grid {
                font-size: 12px !important;
            }

            #invoice .print-terbilang-body {
                font-size: 15px !important;
                line-height: 1.45 !important;
            }

            #invoice .print-summary-total {
                font-size: 16px !important;
            }

            #invoice .print-footer {
                margin-top: 26px !important;
                padding-top: 18px !important;
            }

            #invoice .print-footer-list {
                font-size: 8.5px !important;
                line-height: 1.2 !important;
            }

            #invoice .print-footer-row {
                grid-template-columns: 70px minmax(0, 2.9fr) 118px 1.15fr !important;
                gap: 6px !important;
            }

            #invoice .print-footer-row > div {
                white-space: nowrap !important;
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
        <div class="print-header flex items-start justify-between gap-8">
            <div class="pt-1">
                <div class="text-[10px] font-semibold uppercase tracking-[0.28em] text-slate-500">Logo Kiri</div>
                <div class="print-logo-box mt-2 flex h-20 w-48 items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 text-sm font-semibold text-slate-400">
                    Placeholder Logo
                </div>
            </div>
            <div class="text-right">
                <div class="text-[10px] font-semibold uppercase tracking-[0.28em] text-slate-500">Logo Kanan</div>
                <div class="print-logo-box mt-2 ml-auto flex h-20 w-48 items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 text-sm font-semibold text-slate-400">
                    Placeholder Logo
                </div>
                <div class="print-title mt-6 text-5xl font-black tracking-tight text-slate-900">Invoice</div>
            </div>
        </div>

        <div class="print-main-grid mt-10 grid grid-cols-1 gap-8 md:grid-cols-[1.15fr_0.85fr]">
            <div>
                <div class="print-branch-name text-2xl font-extrabold text-slate-900">{{ $branch?->name ?? 'BMPNET' }}</div>
                <div class="print-branch-address mt-3 space-y-1 text-base font-semibold leading-relaxed text-slate-800">
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
                <div class="print-meta-grid grid grid-cols-[110px_1fr] gap-x-4 gap-y-2 text-sm">
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
                    <div class="bg-slate-100 px-3 py-1 text-sm font-bold text-slate-800">Bill To :</div>
                    <div class="print-billto-body mt-2 text-sm leading-relaxed text-slate-800">
                        <div class="font-bold">{{ $invoice->client->name }}</div>
                        @if($invoice->client->address)
                            <div>{{ $invoice->client->address }}</div>
                        @endif
                        @if($invoice->client->city)
                            <div>{{ $invoice->client->city }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="print-section-gap mt-12 overflow-hidden rounded-2xl border border-slate-200">
            <table class="w-full border-collapse">
                <thead class="bg-slate-50">
                    <tr class="print-table-head text-left text-[11px] font-bold uppercase tracking-[0.18em] text-slate-600">
                        <th class="print-table-cell px-5 py-4 w-16 border-b border-slate-200">No.</th>
                        <th class="print-table-cell px-5 py-4 border-b border-slate-200">Description</th>
                        <th class="print-table-cell px-5 py-4 w-24 border-b border-slate-200 text-center">QYT</th>
                        <th class="print-table-cell px-5 py-4 w-48 border-b border-slate-200 text-right">Price</th>
                        <th class="print-table-cell px-5 py-4 w-48 border-b border-slate-200 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="print-table-body text-sm text-slate-800">
                    @foreach($invoice->items as $index => $item)
                        <tr>
                            <td class="print-table-cell px-5 py-4 align-top border-b border-slate-100">{{ $index + 1 }}</td>
                            <td class="print-table-cell px-5 py-4 align-top border-b border-slate-100">{{ $item->description }}</td>
                            <td class="print-table-cell px-5 py-4 align-top border-b border-slate-100 text-center">{{ $item->qty }}</td>
                            <td class="print-table-cell px-5 py-4 align-top border-b border-slate-100 text-right">{{ number_format($item->billing_base_amount, 0, ',', '.') }}</td>
                            <td class="print-table-cell px-5 py-4 align-top border-b border-slate-100 text-right">{{ number_format($item->billing_line_total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="print-bottom-grid mt-8 grid grid-cols-1 gap-8 md:grid-cols-[1fr_340px]">
            <div class="pt-3">
                <div class="print-terbilang-title text-base font-bold text-slate-800">Amount in words / Terbilang</div>
                <div class="print-terbilang-body mt-3 max-w-2xl text-lg italic leading-relaxed text-slate-900">
                    {{ $invoice->amount_in_words }}
                </div>
                @if(filled($invoice->notes) && $invoice->notes !== 'Tagihan Bulanan Otomatis')
                    <div class="mt-8 text-sm text-slate-500">
                        {{ $invoice->notes }}
                    </div>
                @endif
            </div>

            <div class="rounded-2xl border border-slate-200">
                <div class="print-summary-grid grid grid-cols-[1fr_150px] text-sm">
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

                    <div class="print-summary-total px-5 py-4 text-lg font-extrabold text-slate-900">Total Tagihan</div>
                    <div class="print-summary-total px-5 py-4 text-right text-lg font-extrabold text-slate-900">
                        {{ number_format($billingSummary['total_amount'], 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

        <div class="print-footer mt-12 border-t border-slate-200 pt-8">
            <div class="border-t-4 border-blue-800 pt-5">
                <div class="print-footer-list space-y-1.5 text-sm text-slate-900">
                    @foreach(($global_branches ?? collect())->sortBy('name') as $footerBranch)
                        @php
                            $branchEmail = $footerBranch->code ? 'cs@' . strtolower($footerBranch->code) . '.bmp.net.id' : 'cs@bmp.net.id';
                        @endphp
                        <div class="print-footer-row grid grid-cols-[100px_1fr] gap-x-3 md:grid-cols-[120px_minmax(0,2.35fr)_180px_1.35fr] md:items-start">
                            <div class="font-extrabold">{{ $footerBranch->name }}</div>
                            <div>{{ $footerBranch->address ?: '-' }}</div>
                            <div class="font-semibold">Tel : {{ $footerBranch->phone ?: '-' }}</div>
                            <div>e-mail : {{ $branchEmail }}</div>
                        </div>
                    @endforeach
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
