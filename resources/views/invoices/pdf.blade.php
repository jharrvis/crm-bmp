<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; }
        .header { width: 100%; margin-bottom: 30px; }
        .header table { width: 100%; border: none; }
        .company-name { font-size: 24px; font-weight: bold; color: #1e3a8a; }
        .invoice-title { font-size: 28px; font-weight: bold; text-align: right; color: #0f172a; }
        .section-title { font-size: 14px; font-weight: bold; margin-bottom: 5px; color: #475569; }
        .info-table { width: 100%; margin-bottom: 30px; }
        .info-table td { vertical-align: top; width: 50%; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th { background-color: #f1f5f9; padding: 10px; text-align: left; border-bottom: 2px solid #cbd5e1; }
        .items-table td { padding: 10px; border-bottom: 1px solid #e2e8f0; }
        .items-table .amount { text-align: right; }
        .summary-table { width: 100%; margin-bottom: 30px; }
        .summary-table td { padding: 5px 10px; }
        .summary-table .label { text-align: right; font-weight: bold; }
        .summary-table .value { text-align: right; width: 150px; }
        .summary-table .grand-total { font-size: 16px; font-weight: bold; background-color: #f8fafc; }
        .footer { margin-top: 50px; font-size: 11px; color: #64748b; }
        .status { padding: 5px 10px; border-radius: 4px; font-weight: bold; font-size: 14px; display: inline-block; }
        .status-paid { background-color: #dcfce7; color: #166534; }
        .status-unpaid { background-color: #fee2e2; color: #991b1b; }
        .signature-box { text-align: center; margin-top: 40px; float: right; width: 200px; }
        .signature-img { max-height: 80px; margin-bottom: 10px; }
    </style>
</head>
<body>
    @php
        $statusLabels = [
            'draft' => 'Draft',
            'unpaid' => 'Belum Lunas',
            'partially_paid' => 'Dibayar Sebagian',
            'paid' => 'Lunas',
            'overdue' => 'Terlambat',
            'cancelled' => 'Batal',
        ];
        $statusClass = in_array($invoice->status, ['paid']) ? 'status-paid' : 'status-unpaid';
    @endphp

    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="company-name">{{ config('app.name', 'BMPnet') }}</div>
                    <div>Branch: {{ $invoice->client->branch->name ?? 'Pusat' }}</div>
                </td>
                <td style="text-align: right;">
                    <div class="invoice-title">INVOICE</div>
                    <div class="status {{ $statusClass }}">{{ strtoupper($statusLabels[$invoice->status] ?? $invoice->status) }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="info-table">
        <tr>
            <td>
                <div class="section-title">Ditagihkan Kepada:</div>
                <div style="font-weight: bold; font-size: 14px; margin-bottom: 5px;">{{ $invoice->client->name }}</div>
                <div>{{ $invoice->client->address }}</div>
                <div>{{ $invoice->client->city }}</div>
                <div style="margin-top: 10px;">ID Pelanggan: {{ $invoice->client->client_code }}</div>
            </td>
            <td>
                <table style="width: 100%;">
                    <tr>
                        <td style="color: #64748b;">No. Invoice:</td>
                        <td style="font-weight: bold; text-align: right;">{{ $invoice->invoice_number }}</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b;">Tgl Invoice:</td>
                        <td style="font-weight: bold; text-align: right;">{{ $invoice->invoice_date->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b;">Jatuh Tempo:</td>
                        <td style="font-weight: bold; text-align: right;">{{ $invoice->due_date?->format('d M Y') }}</td>
                    </tr>
                    @if($invoice->paid_at)
                    <tr>
                        <td style="color: #64748b;">Tgl Lunas:</td>
                        <td style="font-weight: bold; text-align: right;">{{ $invoice->paid_at->format('d M Y') }}</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Deskripsi Layanan</th>
                <th class="amount" style="width: 60px;">Qty</th>
                <th class="amount" style="width: 100px;">Harga</th>
                <th class="amount" style="width: 120px;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td class="amount">{{ $item->qty }}</td>
                <td class="amount">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                <td class="amount">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table style="width: 100%;">
        <tr>
            <td style="vertical-align: top; width: 50%;">
                <div class="section-title">Catatan:</div>
                <div style="color: #475569;">
                    {!! nl2br(e($invoice->notes ?: 'Terima kasih atas kepercayaan Anda menggunakan layanan kami.')) !!}
                </div>
            </td>
            <td style="vertical-align: top; width: 50%;">
                <table class="summary-table">
                    <tr>
                        <td class="label">Subtotal</td>
                        <td class="value">Rp {{ number_format($invoice->subtotal_amount, 0, ',', '.') }}</td>
                    </tr>
                    
                    @if($invoice->uses_tax && $invoice->tax_amount > 0)
                    <tr>
                        <td class="label">PPN ({{ (int) $invoice->tax_rate }}%)</td>
                        <td class="value">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    
                    @if($invoice->discount_amount > 0)
                    <tr>
                        <td class="label">Diskon</td>
                        <td class="value">- Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    
                    <tr>
                        <td class="label grand-total">Total Tagihan</td>
                        <td class="value grand-total">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div style="clear: both;"></div>

    @if($invoice->signature_path)
    <div class="signature-box">
        <div style="margin-bottom: 10px; color: #475569;">Hormat Kami,</div>
        <img src="{{ storage_path('app/public/' . $invoice->signature_path) }}" class="signature-img" alt="Signature">
        <div style="font-weight: bold; border-top: 1px solid #cbd5e1; padding-top: 5px;">Bagian Keuangan</div>
    </div>
    @endif

    <div style="clear: both;"></div>
    
    <div class="footer">
        Dicetak otomatis dari sistem pada {{ now()->format('d M Y H:i') }}
    </div>
</body>
</html>
