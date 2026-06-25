<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        if ($user = $request->user()) {
            activity('auth')
                ->causedBy($user)
                ->event('login')
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => (string) $request->userAgent(),
                ])
                ->log('User login');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user) {
            activity('auth')
                ->causedBy($user)
                ->event('logout')
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => (string) $request->userAgent(),
                ])
                ->log('User logout');
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
