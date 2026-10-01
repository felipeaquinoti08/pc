<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view - SSO landing page, unless SSO isn't configured
     * yet, in which case show the local form directly so the admin isn't
     * locked out before configuring it.
     */
    public function create(): View
    {
        if (! Setting::current()->ssoConfigured()) {
            return view('auth.login-local');
        }

        return view('auth.login');
    }

    /**
     * Display the local username/password login form - the fallback used
     * when Microsoft Entra SSO is unavailable.
     */
    public function createLocal(): View
    {
        return view('auth.login-local');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
