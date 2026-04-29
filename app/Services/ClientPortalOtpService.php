<?php

namespace App\Services;

use App\Mail\ClientPortalOtpMail;
use App\Models\ClientPortalAccount;
use App\Models\ClientPortalOtp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ClientPortalOtpService
{
    public function requestOtp(string $email, ?string $ipAddress = null): ClientPortalAccount
    {
        $email = Str::lower(trim($email));
        $rateKey = $this->requestRateKey($email, $ipAddress);

        if (RateLimiter::tooManyAttempts($rateKey, config('client_portal.otp_request_limit'))) {
            $seconds = RateLimiter::availableIn($rateKey);

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak permintaan OTP. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        $account = ClientPortalAccount::with('client.primaryContact')
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (! $account || ! $account->isActive()) {
            throw ValidationException::withMessages([
                'email' => 'Email portal client tidak ditemukan atau belum aktif.',
            ]);
        }

        ClientPortalOtp::query()
            ->where('client_portal_account_id', $account->id)
            ->whereNull('verified_at')
            ->update(['expires_at' => now()]);

        $otpCode = str_pad((string) random_int(0, (10 ** config('client_portal.otp_length')) - 1), config('client_portal.otp_length'), '0', STR_PAD_LEFT);

        ClientPortalOtp::create([
            'client_portal_account_id' => $account->id,
            'email' => $account->email,
            'code_hash' => Hash::make($otpCode),
            'expires_at' => now()->addMinutes(config('client_portal.otp_ttl_minutes')),
            'attempt_count' => 0,
            'request_ip' => $ipAddress,
            'sent_at' => now(),
        ]);

        Mail::to($account->email)->send(
            new ClientPortalOtpMail(
                clientName: $account->client->name,
                otpCode: $otpCode,
                ttlMinutes: config('client_portal.otp_ttl_minutes')
            )
        );

        RateLimiter::hit($rateKey, config('client_portal.otp_request_decay_seconds'));

        return $account;
    }

    public function latestPendingOtp(ClientPortalAccount $account, string $email): ?ClientPortalOtp
    {
        return $account->otpCodes()
            ->where('email', Str::lower(trim($email)))
            ->whereNull('verified_at')
            ->latest('id')
            ->first();
    }

    private function requestRateKey(string $email, ?string $ipAddress): string
    {
        return 'client-portal:otp-request:' . sha1($email . '|' . ($ipAddress ?? 'unknown'));
    }
}
