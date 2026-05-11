<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateClubMemberRoleRequest;
use App\Models\Club;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ClubMemberController extends Controller
{
    public function index(Club $club): JsonResponse
    {
        $this->authorize('view', $club);

        $members = DB::table('club_user')
            ->join('users', 'users.id', '=', 'club_user.user_id')
            ->where('club_user.club_id', $club->getKey())
            ->orderBy('users.name')
            ->select(['users.id', 'users.name', 'users.email', 'club_user.role', 'club_user.joined_at'])
            ->get();

        return response()->json($members);
    }

    public function update(UpdateClubMemberRoleRequest $request, Club $club, User $user): JsonResponse
    {
        $this->authorize('manageMembers', $club);

        $role = $request->string('role')->toString();
        $this->authorize('assignRole', [$club, $role]);

        $membershipExists = DB::table('club_user')
            ->where('club_id', $club->getKey())
            ->where('user_id', $user->getKey())
            ->exists();

        if (! $membershipExists) {
            return response()->json(['message' => 'Member not found in club.'], 404);
        }

        $currentRole = $user->clubRoleFor((int) $club->getKey());

        if (in_array($currentRole, ['manager', 'owner'], true) && ! in_array($role, ['manager', 'owner'], true)) {
            $managerCount = DB::table('club_user')
                ->where('club_id', $club->getKey())
                ->whereIn('role', ['manager', 'owner'])
                ->count();
            if ($managerCount <= 1) {
                return response()->json(['message' => 'Club must keep at least one manager.'], 422);
            }
        }

        $club->users()->updateExistingPivot($user->getKey(), ['role' => $role]);

        return response()->json(['message' => 'Member role updated.']);
    }

    public function destroy(Club $club, User $user): JsonResponse
    {
        $this->authorize('manageMembers', $club);

        $membershipExists = DB::table('club_user')
            ->where('club_id', $club->getKey())
            ->where('user_id', $user->getKey())
            ->exists();

        if (! $membershipExists) {
            return response()->json(['message' => 'Member not found in club.'], 404);
        }

        $currentRole = $user->clubRoleFor((int) $club->getKey());

        if (in_array($currentRole, ['manager', 'owner'], true)) {
            $managerCount = DB::table('club_user')
                ->where('club_id', $club->getKey())
                ->whereIn('role', ['manager', 'owner'])
                ->count();
            if ($managerCount <= 1) {
                return response()->json(['message' => 'Club must keep at least one manager.'], 422);
            }
        }

        $club->users()->detach($user->getKey());

        return response()->json([], 204);
    }
}
