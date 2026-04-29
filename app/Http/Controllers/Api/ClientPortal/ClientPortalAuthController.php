<?php

namespace App\Http\Controllers\Api\ClientPortal;

use App\Http\Controllers\Controller;
use App\Models\ClientPortalAccount;
use App\Models\ClientPortalSession;
use App\Services\ClientPortalOtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ClientPortalAuthController extends Controller
{
    public function __construct(
        protected ClientPortalOtpService $otpService
    ) {
    }

    public function requestOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $this->otpService->requestOtp($validated['email'], $request->ip());

        return response()->json([
            'success' => true,
            'message' => 'OTP berhasil dikirim ke email terdaftar.',
            'expires_in_minutes' => config('client_portal.otp_ttl_minutes'),
        ]);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string',
            'device_name' => 'nullable|string|max:120',
        ]);

        $account = ClientPortalAccount::with('client.primaryContact')
            ->whereRaw('LOWER(email) = ?', [Str::lower(trim($validated['email']))])
            ->first();

        if (! $account || ! $account->isActive()) {
            throw ValidationException::withMessages([
                'email' => 'Akun portal client tidak ditemukan atau belum aktif.',
            ]);
        }

        $otp = $this->otpService->latestPendingOtp($account, $validated['email']);

        if (! $otp || $otp->isExpired()) {
            throw ValidationException::withMessages([
                'otp' => 'OTP tidak ditemukan atau sudah kedaluwarsa.',
            ]);
        }

        if ($otp->attempt_count >= config('client_portal.otp_max_attempts')) {
            throw ValidationException::withMessages([
                'otp' => 'Jumlah percobaan OTP melebihi batas.',
            ]);
        }

        if (! Hash::check($validated['otp'], $otp->code_hash)) {
            $otp->increment('attempt_count');

            throw ValidationException::withMessages([
                'otp' => 'Kode OTP tidak valid.',
            ]);
        }

        $otp->forceFill([
            'verified_at' => now(),
            'attempt_count' => $otp->attempt_count + 1,
        ])->save();

        $plainTextToken = Str::random(80);

        $session = ClientPortalSession::create([
            'client_portal_account_id' => $account->id,
            'token_hash' => hash('sha256', $plainTextToken),
            'device_name' => $validated['device_name'] ?? 'Unknown Device',
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'last_used_at' => now(),
            'expires_at' => now()->addDays(config('client_portal.token_ttl_days')),
        ]);

        $account->forceFill([
            'email_verified_at' => $account->email_verified_at ?? now(),
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        return response()->json([
            'success' => true,
            'token' => $plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $session->expires_at?->toIso8601String(),
            'client' => [
                'id' => $account->client->id,
                'client_code' => $account->client->client_code,
                'name' => $account->client->name,
                'email' => $account->email,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var ClientPortalSession|null $session */
        $session = $request->attributes->get('client_portal_session');

        if ($session) {
            $session->forceFill(['revoked_at' => now()])->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Sesi portal client berhasil ditutup.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var ClientPortalAccount $account */
        $account = $request->user();
        $account->loadMissing('client.primaryContact');

        return response()->json([
            'account' => [
                'id' => $account->id,
                'email' => $account->email,
                'status' => $account->status,
                'last_login_at' => $account->last_login_at?->toIso8601String(),
            ],
            'client' => [
                'id' => $account->client->id,
                'client_code' => $account->client->client_code,
                'name' => $account->client->name,
                'status' => $account->client->status,
                'address' => $account->client->address,
                'city' => $account->client->city,
                'primary_contact' => $account->client->primaryContact ? [
                    'name' => $account->client->primaryContact->name,
                    'email' => $account->client->primaryContact->email,
                    'phone' => $account->client->primaryContact->phone,
                    'whatsapp' => $account->client->primaryContact->whatsapp,
                ] : null,
            ],
        ]);
    }
}
