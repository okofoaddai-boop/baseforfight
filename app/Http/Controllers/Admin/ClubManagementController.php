<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\ClubJoinRequest;
use App\Models\ClubMembership;
use App\Models\ClubMembershipRole;
use App\Models\User;
use App\Services\ClubPermissionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClubManagementController extends Controller
{
    public function __construct(private readonly ClubPermissionService $permissions)
    {
    }

    public function index(Request $request): View
    {
        $user         = $request->user();
        $isSuperAdmin = (bool) $user?->isSuperAdmin();

        if ($isSuperAdmin) {
            $clubs = Club::query()->orderBy('name')->get();

            $joinRequests = ClubJoinRequest::query()
                ->where('status', 'pending')
                ->with(['user', 'club'])
                ->orderByDesc('id')
                ->get();
        } else {
            $clubIds = ClubMembershipRole::query()
                ->where('role', ClubMembershipRole::ROLE_CLUB_MANAGER)
                ->whereHas('membership', fn ($q) => $q->where('user_id', $user->getKey()))
                ->with('membership')
                ->get()
                ->pluck('membership.club_id');

            $clubs = Club::query()
                ->whereIn('id', $clubIds)
                ->orderBy('name')
                ->get();

            $joinRequests = ClubJoinRequest::query()
                ->whereIn('club_id', $clubIds)
                ->where('status', 'pending')
                ->with(['user', 'club'])
                ->orderByDesc('id')
                ->get();
        }

        $memberships = ClubMembership::query()
            ->whereIn('club_id', $clubs->pluck('id'))
            ->with(['user', 'roles', 'club'])
            ->get()
            ->groupBy('club_id');

        $allUsers = $isSuperAdmin
            ? User::query()->orderBy('name')->select(['id', 'name', 'email'])->get()
            : collect();

        return view('admin.clubs.index', [
            'clubs'        => $clubs,
            'joinRequests' => $joinRequests,
            'memberships'  => $memberships,
            'allUsers'     => $allUsers,
            'isSuperAdmin' => $isSuperAdmin,
            'roleLabels'   => ClubMembershipRole::ROLE_LABELS,
            'allRoles'     => ClubMembershipRole::ALL_ROLES,
        ]);
    }

    public function approveJoinRequest(Request $request, ClubJoinRequest $clubJoinRequest): RedirectResponse
    {
        $club = $clubJoinRequest->club;

        if (! $club) {
            abort(404);
        }

        $this->authorize('manageMembers', $club);

        $this->permissions->addMembership(
            $clubJoinRequest->user,
            (int) $club->getKey(),
            [ClubMembershipRole::ROLE_TRAINER]
        );

        $clubJoinRequest->update([
            'status'              => 'approved',
            'reviewed_by_user_id' => $request->user()->getKey(),
            'reviewed_at'         => now(),
        ]);

        return redirect()->route('admin.clubs.index')->with('status', 'Beitrittsanfrage freigegeben.');
    }

    public function declineJoinRequest(Request $request, ClubJoinRequest $clubJoinRequest): RedirectResponse
    {
        $club = $clubJoinRequest->club;

        if (! $club) {
            abort(404);
        }

        $this->authorize('manageMembers', $club);

        $clubJoinRequest->update([
            'status'              => 'declined',
            'reviewed_by_user_id' => $request->user()->getKey(),
            'reviewed_at'         => now(),
        ]);

        return redirect()->route('admin.clubs.index')->with('status', 'Beitrittsanfrage abgelehnt.');
    }

    public function assignUserToClub(Request $request, Club $club): RedirectResponse
    {
        $this->authorize('manageMembers', $club);

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'roles'   => ['required', 'array', 'min:1'],
            'roles.*' => ['required', 'string', 'in:' . implode(',', ClubMembershipRole::ALL_ROLES)],
        ]);

        $target = User::query()->findOrFail($validated['user_id']);

        $this->permissions->addMembership($target, (int) $club->getKey(), $validated['roles']);

        return redirect()->route('admin.clubs.index')->with('status', $target->name . ' wurde dem Verein zugewiesen.');
    }

    public function updateMemberRoles(Request $request, Club $club, User $member): RedirectResponse
    {
        $this->authorize('manageMembers', $club);

        $actor = $request->user();

        $validated = $request->validate([
            'roles'   => ['required', 'array'],
            'roles.*' => ['required', 'string', 'in:' . implode(',', ClubMembershipRole::ALL_ROLES)],
        ]);

        $newRoles = $validated['roles'];

        if ((int) $actor->getKey() === (int) $member->getKey()
            && ! in_array(ClubMembershipRole::ROLE_CLUB_MANAGER, $newRoles, true)
            && $member->hasClubRole((int) $club->getKey(), ClubMembershipRole::ROLE_CLUB_MANAGER)
        ) {
            return back()->withErrors(['roles' => 'Du kannst dir selbst die Club-Manager-Rolle nicht entziehen.']);
        }

        if (! in_array(ClubMembershipRole::ROLE_CLUB_MANAGER, $newRoles, true)
            && $member->hasClubRole((int) $club->getKey(), ClubMembershipRole::ROLE_CLUB_MANAGER)
        ) {
            $remaining = $this->permissions->countClubManagers((int) $club->getKey());

            if ($remaining <= 1) {
                return back()->withErrors(['roles' => 'Der Verein muss mindestens einen Club-Manager behalten.']);
            }
        }

        $this->permissions->syncRoles($member, (int) $club->getKey(), $newRoles);

        return redirect()->route('admin.clubs.index')->with('status', 'Rollen aktualisiert.');
    }

    public function removeMember(Request $request, Club $club, User $member): RedirectResponse
    {
        $this->authorize('manageMembers', $club);

        if ($member->hasClubRole((int) $club->getKey(), ClubMembershipRole::ROLE_CLUB_MANAGER)) {
            $remaining = $this->permissions->countClubManagers((int) $club->getKey());

            if ($remaining <= 1) {
                return back()->withErrors(['member' => 'Der Verein muss mindestens einen Club-Manager behalten.']);
            }
        }

        $this->permissions->removeMembership($member, (int) $club->getKey());

        return redirect()->route('admin.clubs.index')->with('status', $member->name . ' wurde aus dem Verein entfernt.');
    }
}
