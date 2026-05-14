<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectLine }}</title>
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <div style="max-width:640px;margin:0 auto;padding:32px 16px;">
        <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:24px;padding:32px;">
            <div style="font-size:28px;font-weight:700;color:#2563eb;margin-bottom:8px;">BMPnet</div>
            <p style="margin:0 0 24px;font-size:14px;color:#64748b;">Notifikasi Ticket Support</p>

            <h1 style="margin:0 0 16px;font-size:24px;line-height:1.3;color:#0f172a;">{{ $headline }}</h1>
            <p style="margin:0 0 24px;font-size:15px;line-height:1.7;color:#334155;">Halo {{ $recipientName }},</p>
            <p style="margin:0 0 24px;font-size:15px;line-height:1.7;color:#334155;">{{ $messageBody }}</p>

            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:18px;padding:18px 20px;margin-bottom:24px;">
                <div style="font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#64748b;margin-bottom:8px;">Ticket</div>
                <div style="font-size:15px;font-weight:700;color:#0f172a;margin-bottom:4px;">{{ $ticketNumber }}</div>
                <div style="font-size:15px;color:#334155;">{{ $ticketSubject }}</div>
            </div>

            @if($actionUrl && $actionLabel)
                <div style="margin-bottom:24px;">
                    <a href="{{ $actionUrl }}" style="display:inline-block;padding:12px 20px;background:#2563eb;color:#ffffff;text-decoration:none;border-radius:14px;font-weight:700;">
                        {{ $actionLabel }}
                    </a>
                </div>
            @endif

            <p style="margin:0;font-size:13px;line-height:1.7;color:#64748b;">
                Email ini dikirim otomatis oleh sistem ticket BMPnet. Jika Anda membutuhkan bantuan lanjutan, silakan balas melalui kanal yang sesuai.
            </p>
        </div>
    </div>
</body>
</html>
