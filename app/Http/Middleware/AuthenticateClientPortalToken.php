<?php

namespace App\Http\Middleware;

use App\Models\ClientPortalSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateClientPortalToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $plainTextToken = $request->bearerToken();

        if (! $plainTextToken) {
            return response()->json(['message' => 'Token autentikasi portal client tidak ditemukan.'], 401);
        }

        $session = ClientPortalSession::with('account.client.primaryContact')
            ->where('token_hash', hash('sha256', $plainTextToken))
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $session || ! $session->account || ! $session->account->isActive()) {
            return response()->json(['message' => 'Token autentikasi portal client tidak valid atau sudah kedaluwarsa.'], 401);
        }

        $session->forceFill(['last_used_at' => now()])->save();

        $request->attributes->set('client_portal_session', $session);
        $request->attributes->set('client_portal_account', $session->account);
        $request->setUserResolver(static fn () => $session->account);

        return $next($request);
    }
}
