<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\RendersStatusPatches;
use App\Http\Controllers\Concerns\ScopesAdminQueries;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * The account roster, and the only place a role is handed out.
 *
 * Two interfaces mean two roles: Resident and City Administrator. The older
 * barangay-level values ('admin', 'staff') still exist in rows written before
 * the fold, so they are read everywhere and offered nowhere - picking
 * "City Administrator" writes 'city_admin' and a legacy account normalises the
 * moment anyone edits it.
 */
class UserController extends Controller
{
    use RendersStatusPatches, ScopesAdminQueries;

    /** The two roles an officer can assign. */
    public const ROLES = User::ROLES;

    /**
     * Role -> badge class. Shared by the board and the in-place patch. The
     * retired values are listed so an old row is still coloured as the officer
     * it already is, rather than falling through to neutral.
     */
    public const ROLE_BADGES = [
        'city_admin' => 'badge-violet',
        'admin' => 'badge-violet',
        'staff' => 'badge-violet',
        'resident' => 'badge-neutral',
    ];

    /** Status -> badge class. Shared by the board and the in-place patch. */
    public const STATUS_BADGES = [
        'active' => 'badge-green',
        'suspended' => 'badge-rose',
    ];

    public const STATUSES = [
        'active' => 'Active',
        'suspended' => 'Suspended',
    ];

    public function index(Request $request): View
    {
        $query = $this->scopedUsers(User::query())->withCount([
            'complaints',
            'documentRequests as requests_count',
            'appointments as appointments_count',
        ]);

        if ($request->filled('q')) {
            $term = trim($request->string('q')->toString());
            $query->where(fn ($q) => $q->where('name', 'like', '%'.$term.'%')
                ->orWhere('email', 'like', '%'.$term.'%')
                ->orWhere('purok', 'like', '%'.$term.'%'));
        }

        if ($request->input('role') === 'resident') {
            $query->where('role', 'resident');
        } elseif ($request->input('role') === 'city_admin') {
            /* One tile for both interfaces, so asking for administrators has to
               match the retired officer values too. */
            $query->whereIn('role', User::OFFICIAL_ROLES);
        }

        if (in_array($request->input('status'), array_keys(self::STATUSES), true)) {
            $query->where('status', $request->input('status'));
        }

        $users = $query->orderBy('role')->orderBy('name')->paginate(12)->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            /* One tile per interface, not per stored value: an officer with a
               legacy 'staff' row belongs in the City Administrators count. */
            'counts' => [
                'all' => $this->scopedUsers(User::query())->count(),
                'resident' => $this->countRole('resident'),
                'city_admin' => $this->countRole('city_admin'),
                'suspended' => $this->scopedUsers(User::query())->where('status', 'suspended')->count(),
            ],
            'scopeLabel' => $this->scopeLabel(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse|JsonResponse
    {
        $me = $request->user();

        $this->guardTarget($user);

        $data = $request->validate([
            'role' => ['required', Rule::in(array_keys(self::ROLES))],
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
        ]);

        if ($user->is($me)) {
            if ($data['status'] === 'suspended') {
                return $this->deny('You cannot suspend your own account.');
            }

            if ($this->portalFor($data['role']) !== $this->portalFor($user->role)) {
                return $this->deny('You cannot move your own account to the other interface. Ask another administrator to do it.');
            }
        }

        if ($this->isLastOfficial($user, $data['role'])) {
            return $this->deny('This is the last City Administrator account — promote another officer first.');
        }

        $user->update($data);

        $label = self::ROLES[$data['role']].' — '.self::STATUSES[$data['status']];
        $message = $user->name.' is now '.$label.'.';

        return $this->savedResponse(
            $request,
            'admin.users.index',
            $message,
            [
                /* The avatar colour follows the role, so the identity cell moves too. */
                [
                    'selector' => sprintf('[data-record-id="%d"] [data-identity-cell]', $user->id),
                    'view' => 'admin.users.partials.identity',
                    'data' => ['user' => $user, 'me' => $me->id],
                ],
                [
                    'selector' => sprintf('[data-record-id="%d"] [data-role-cell]', $user->id),
                    'badge' => self::ROLE_BADGES[$user->role] ?? 'badge-neutral',
                    'label' => $user->role_label,
                ],
                [
                    'selector' => $this->statusCellSelector($user->id),
                    'badge' => self::STATUS_BADGES[$user->status] ?? 'badge-neutral',
                    'label' => self::STATUSES[$user->status] ?? ucfirst($user->status),
                ],
                /* The Manage button prefills the modal from these, so they have
                   to follow the save or the next open offers a stale value. */
                [
                    'selector' => sprintf('[data-record-id="%d"] [data-modal-open="manageUser"]', $user->id),
                    'attrs' => [
                        /* Always one of the two values the select offers, even
                           when the row itself still carries a retired role. */
                        'data-role' => $data['role'],
                        'data-status' => $user->status,
                        'data-name' => $user->name,
                    ],
                ],
            ],
        );
    }

    public function destroy(Request $request, User $user): RedirectResponse|JsonResponse
    {
        $me = $request->user();

        $this->guardTarget($user);

        if ($user->is($me)) {
            return $this->deny('You cannot delete your own account.');
        }

        if ($this->isLastOfficial($user, 'resident')) {
            return $this->deny('This is the last City Administrator account — promote another officer first.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', $name.' was removed from the roster.');
    }

    /**
     * Which interface a role opens. 'official' is accepted as well, so the
     * roster can be filtered by interface rather than by stored string.
     */
    private function portalFor(string $role): string
    {
        return in_array($role, User::OFFICIAL_ROLES, true) ? 'city_admin' : 'resident';
    }

    /** Accounts that open one interface, inside the barangay on screen. */
    private function countRole(string $role): int
    {
        $query = $this->scopedUsers(User::query());

        return $role === 'resident'
            ? $query->where('role', 'resident')->count()
            : $query->whereIn('role', User::OFFICIAL_ROLES)->count();
    }

    /**
     * Every officer is a City Administrator, so any of them may act on any
     * account. The check is left in place as the seam for a future
     * barangay-level permission; see ScopesAdminQueries::scopeBarangayId().
     */
    private function guardTarget(User $target): void
    {
        $id = $this->scopeBarangayId();

        if ($id !== null && $target->barangay_id !== $id) {
            abort(404);
        }
    }

    /**
     * True when the change would leave the city with no administrator at all.
     *
     * Counted across every barangay rather than one, because losing the last
     * officer anywhere would lock the console for the whole city.
     */
    private function isLastOfficial(User $user, string $newRole): bool
    {
        if (! $user->isOfficial() || $this->portalFor($newRole) === 'city_admin') {
            return false; // never an officer, or staying one.
        }

        return User::query()
            ->whereIn('role', User::OFFICIAL_ROLES)
            ->whereKeyNot($user->id)
            ->count() === 0;
    }

    /**
     * A refusal the officer can read. Ajax gets it as JSON so the toast can
     * say why nothing changed; a plain post gets the usual redirect + flash.
     */
    private function deny(string $message): RedirectResponse|JsonResponse
    {
        if (request()->expectsJson()) {
            return response()->json(['message' => $message], 422);
        }

        return redirect()->route('admin.users.index')->with('error', $message);
    }
}
