<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientPortalNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientPortalAccountController extends Controller
{
    public function store(Request $request, Client $client): JsonResponse
    {
        $validated = $this->validatedData($request);

        $account = $client->portalAccount()->create([
            ...$validated,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Akun portal client berhasil dibuat.',
            'account' => $this->accountPayload($client->fresh('portalAccount.sessions')),
        ]);
    }

    public function update(Request $request, Client $client): JsonResponse
    {
        $account = $client->portalAccount;
        abort_unless($account !== null, 404);

        $validated = $this->validatedData($request, $account->id);

        $account->update([
            ...$validated,
            'updated_by' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Akun portal client berhasil diperbarui.',
            'account' => $this->accountPayload($client->fresh('portalAccount.sessions')),
        ]);
    }

    public function revokeSessions(Request $request, Client $client): JsonResponse
    {
        $account = $client->portalAccount;
        abort_unless($account !== null, 404);

        $account->sessions()
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->update(['revoked_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Semua sesi portal client berhasil dicabut.',
            'account' => $this->accountPayload($client->fresh('portalAccount.sessions')),
        ]);
    }

    private function validatedData(Request $request, ?int $ignoreAccountId = null): array
    {
        return $request->validate([
            'email' => [
                'required',
                'email',
                Rule::unique('client_portal_accounts', 'email')->ignore($ignoreAccountId),
            ],
            'status' => ['required', Rule::in(['pending', 'active', 'suspended'])],
            'notes' => 'nullable|string',
        ]);
    }

    private function accountPayload(Client $client): array
    {
        $account = $client->portalAccount;

        return [
            'id' => $account->id,
            'email' => $account->email,
            'status' => $account->status,
            'notes' => $account->notes,
            'email_verified_at' => $account->email_verified_at?->toIso8601String(),
            'last_login_at' => $account->last_login_at?->toIso8601String(),
            'last_login_ip' => $account->last_login_ip,
            'active_sessions_count' => $account->sessions
                ->whereNull('revoked_at')
                ->filter(fn ($session) => $session->expires_at?->isFuture())
                ->count(),
            'unread_notifications_count' => ClientPortalNotification::query()
                ->where('client_id', $client->id)
                ->whereNull('read_at')
                ->count(),
        ];
    }
}
