<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Native (dependency-free) user impersonation, restricted to Super Admin.
 *
 * start(): stores the real admin's id in the session, logs in as the target,
 * and drops them into the employee self-service app. stop(): restores the
 * original admin. A Super Admin may never impersonate another Admin/Super Admin.
 */
class ImpersonationController extends Controller
{
    public function start(User $user)
    {
        $me = Auth::user();

        // Only a Super Admin may impersonate. They may impersonate Admins too, but
        // never themselves or another Super Admin.
        abort_unless($me && $me->isSuperAdmin(), 403);
        if ($user->id === $me->id || $user->isSuperAdmin()) {
            return redirect()->back()->with('error', 'You cannot impersonate this user.');
        }
        // Prevent nested impersonation.
        if (session()->has('impersonator_id')) {
            return redirect()->back()->with('error', 'Already impersonating a user.');
        }

        session(['impersonator_id' => $me->id]);
        Log::info("Impersonation start: Super Admin {$me->id} ({$me->email}) -> user {$user->id} ({$user->email}).");

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'You are now impersonating ' . $user->name . '.');
    }

    public function stop()
    {
        $impersonatorId = session('impersonator_id');
        if (! $impersonatorId) {
            return redirect()->route('dashboard');
        }

        $original = User::find($impersonatorId);
        session()->forget('impersonator_id');

        if (! $original) {
            Auth::logout();
            return redirect()->route('login');
        }

        Log::info("Impersonation stop: restored Super Admin {$original->id} ({$original->email}).");
        Auth::login($original);

        // Super Admins live in the Filament panel.
        return redirect('/panel')->with('success', 'Returned to your account.');
    }
}
