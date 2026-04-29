<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kode OTP Portal Client</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f8fafc; color:#0f172a; padding:24px;">
    <div style="max-width:560px; margin:0 auto; background:#ffffff; border:1px solid #e2e8f0; border-radius:16px; padding:32px;">
        <p style="margin:0 0 12px;">Halo {{ $clientName }},</p>
        <p style="margin:0 0 16px;">Gunakan kode OTP berikut untuk login ke portal client:</p>
        <div style="margin:24px 0; padding:16px; text-align:center; background:#eff6ff; border-radius:12px; border:1px solid #bfdbfe;">
            <span style="font-size:32px; font-weight:700; letter-spacing:8px; color:#1d4ed8;">{{ $otpCode }}</span>
        </div>
        <p style="margin:0 0 12px;">Kode ini berlaku selama {{ $ttlMinutes }} menit.</p>
        <p style="margin:0; color:#475569;">Jika Anda tidak merasa meminta login, abaikan email ini.</p>
    </div>
</body>
</html>
