<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ $invoice->invoice_number }}</title>
</head>

<body style="margin:0;padding:24px;background:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <div style="max-width:680px;margin:0 auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:20px;overflow:hidden;">
        <div style="padding:24px 28px;background:#0f172a;color:#ffffff;">
            <div style="font-size:12px;font-weight:700;letter-spacing:0.16em;text-transform:uppercase;opacity:0.75;">Invoice BMPnet</div>
            <div style="margin-top:10px;font-size:28px;font-weight:800;">{{ $invoice->invoice_number }}</div>
        </div>

        <div style="padding:28px;">
            <div style="font-size:15px;line-height:1.7;color:#334155;">{!! nl2br(e($customBody)) !!}</div>

            <div style="margin-top:28px;border:1px solid #e2e8f0;border-radius:16px;padding:18px 20px;background:#f8fafc;">
                <div style="display:flex;justify-content:space-between;gap:16px;margin-bottom:8px;">
                    <span style="font-size:13px;font-weight:700;color:#475569;">Pelanggan</span>
                    <span style="font-size:13px;color:#0f172a;">{{ $invoice->client->name }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;gap:16px;margin-bottom:8px;">
                    <span style="font-size:13px;font-weight:700;color:#475569;">Tanggal Invoice</span>
                    <span style="font-size:13px;color:#0f172a;">{{ optional($invoice->invoice_date)->translatedFormat('d F Y') }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;gap:16px;margin-bottom:8px;">
                    <span style="font-size:13px;font-weight:700;color:#475569;">Jatuh Tempo</span>
                    <span style="font-size:13px;color:#0f172a;">{{ optional($invoice->due_date)->translatedFormat('d F Y') }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;gap:16px;">
                    <span style="font-size:13px;font-weight:700;color:#475569;">Total Tagihan</span>
                    <span style="font-size:15px;font-weight:800;color:#0f172a;">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
