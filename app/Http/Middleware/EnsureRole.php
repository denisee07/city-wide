<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage: ->middleware('role:resident') or ->middleware('role:official')
 *
 * 'official' is shorthand for every role that opens the City Administrator
 * console (User::OFFICIAL_ROLES). The system has two interfaces, so the admin
 * group is gated on that word rather than on a list of role strings that a
 * later rename could silently break.
 *
 * Guests are sent to the login screen; authenticated users without one of the
 * listed roles are redirected to their own dashboard instead of a bare 403.
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('login'));
        }

        if ($user->status === 'suspended') {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Your account has been suspended. Please contact the barangay office.']);
        }

        if (! $this->grants($user, $roles)) {
            return redirect()->to($user->isOfficial() ? route('admin.dashboard') : route('resident.dashboard'))
                ->with('error', 'You are not allowed to access that page.');
        }

        return $next($request);
    }

    /** Does this account hold one of the roles the route asks for? */
    protected function grants(User $user, array $roles): bool
    {
        foreach ($roles as $role) {
            if ($role === 'official' ? $user->isOfficial() : $user->role === $role) {
                return true;
            }
        }

        return false;
    }
}
