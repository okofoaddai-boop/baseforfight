<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateClubMemberRoleRequest;
use App\Models\Club;
use App\Models\ClubMembership;
use App\Models\ClubMembershipRole;
use App\Models\User;
use App\Services\ClubPermissionService;
use Illuminate\Http\JsonResponse;

class ClubMemberController extends Controller
{
    public function __construct(private readonly ClubPermissionService $clubPermissions)
    {
    }

    public function index(Club $club): JsonResponse
    {
        $this->authorize('view', $club);

        $members = ClubMembership::query()
            ->with(['user', 'roles'])
            ->where('club_id', $club->getKey())
            ->get()
            ->map(fn (ClubMembership $m) => [
                'id'        => $m->user->getKey(),
                'name'      => $m->user->name,
                'email'     => $m->user->email,
                'roles'     => $m->roles->pluck('role')->all(),
                'joined_at' => $m->joined_at,
            ])
            ->sortBy('name')
            ->values();

        return response()->json($members);
    }

    public function update(UpdateClubMemberRoleRequest $request, Club $club, User $user): JsonResponse
    {
        $this->authorize('manageMembers', $club);

        $newRoles = array_values(array_unique((array) $request->input('roles', [])));

        foreach ($newRoles as $role) {
            $this->authorize('assignRole', [$club, $role]);
        }

        $membership = ClubMembership::query()
            ->where('club_id', $club->getKey())
            ->where('user_id', $user->getKey())
            ->first();

        if ($membership === null) {
            return response()->json(['message' => 'Member not found in club.'], 404);
        }

        // Guard: club must keep at least one club_manager
        $removingManager = in_array(ClubMembershipRole::ROLE_CLUB_MANAGER, $membership->roles->pluck('role')->all(), true)
            && ! in_array(ClubMembershipRole::ROLE_CLUB_MANAGER, $newRoles, true);

        if ($removingManager && $this->clubPermissions->countClubManagers($club) <= 1) {
            return response()->json(['message' => 'Club must keep at least one club_manager.'], 422);
        }

        $this->clubPermissions->syncRoles($membership, $newRoles);

        return response()->json(['message' => 'Member roles updated.', 'roles' => $newRoles]);
    }

    public function destroy(Club $club, User $user): JsonResponse
    {
        $this->authorize('manageMembers', $club);

        $membership = ClubMembership::query()
            ->where('club_id', $club->getKey())
            ->where('user_id', $user->getKey())
            ->first();

        if ($membership === null) {
            return response()->json(['message' => 'Member not found in club.'], 404);
        }

        $isManager = $membership->roles->contains('role', ClubMembershipRole::ROLE_CLUB_MANAGER);

        if ($isManager && $this->clubPermissions->countClubManagers($club) <= 1) {
            return response()->json(['message' => 'Club must keep at least one club_manager.'], 422);
        }

        $membership->roles()->delete();
        $membership->delete();

        return response()->json([], 204);
    }
}
