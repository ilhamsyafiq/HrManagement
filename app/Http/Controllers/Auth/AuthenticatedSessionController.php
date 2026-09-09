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

        // On a mobile device, send normal users (not Admin / Super Admin) straight
        // to the Clock In/Out page so clocking in is the first thing they see.
        // Admins and desktop users still land on their dashboard. If they were
        // heading somewhere specific before login, intended() still respects that.
        $user = $request->user();
        $isMobile = (bool) preg_match(
            '/Mobile|Android|iPhone|iPod|Windows Phone|IEMobile|BlackBerry|Opera Mini/i',
            (string) $request->userAgent()
        );

        if ($isMobile && $user && ! $user->isSuperAdmin() && ! $user->isAdmin()) {
            return redirect()->intended(route('clock.index', absolute: false));
        }

        return redirect()->intended(route('dashboard', absolute: false));
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
