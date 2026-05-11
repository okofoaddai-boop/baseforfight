<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AcceptClubInvitationRequest;
use App\Http\Requests\Api\V1\StoreClubInvitationRequest;
use App\Models\Club;
use App\Models\ClubInvitation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClubInvitationController extends Controller
{
    public function index(Club $club): JsonResponse
    {
        $this->authorize('manageMembers', $club);

        $invitations = ClubInvitation::query()
            ->where('club_id', $club->getKey())
            ->orderByDesc('id')
            ->get();

        return response()->json($invitations);
    }

    public function store(StoreClubInvitationRequest $request, Club $club): JsonResponse
    {
        $this->authorize('manageMembers', $club);

        $role = $request->string('role')->toString();
        $this->authorize('assignRole', [$club, $role]);

        $email = Str::lower($request->string('email')->toString());

        $invitation = ClubInvitation::query()->create([
            'club_id' => $club->getKey(),
            'email' => $email,
            'role' => $role,
            'token' => Str::random(64),
            'invited_by_user_id' => $request->user()->getKey(),
            'expires_at' => now()->addDays($request->integer('expires_in_days', 14)),
        ]);

        return response()->json($invitation, 201);
    }

    public function accept(AcceptClubInvitationRequest $request): JsonResponse
    {
        $user = $request->user();

        $invitation = ClubInvitation::query()
            ->where('token', $request->string('token')->toString())
            ->whereNull('accepted_at')
            ->first();

        if (! $invitation) {
            return response()->json(['message' => 'Invitation not found.'], 404);
        }

        if ($invitation->expires_at && $invitation->expires_at->isPast()) {
            return response()->json(['message' => 'Invitation has expired.'], 422);
        }

        if (Str::lower($invitation->email) !== Str::lower((string) $user->getAttribute('email'))) {
            return response()->json(['message' => 'Invitation email does not match current user.'], 403);
        }

        $club = $invitation->club;

        if (! $club) {
            return response()->json(['message' => 'Club not found.'], 404);
        }

        $club->users()->syncWithoutDetaching([
            $user->getKey() => [
                'role' => $invitation->role,
                'joined_at' => now(),
            ],
        ]);

        $invitation->update([
            'accepted_by_user_id' => $user->getKey(),
            'accepted_at' => now(),
        ]);

        return response()->json([
            'message' => 'Invitation accepted.',
            'club_id' => $club->getKey(),
            'role' => $invitation->role,
        ]);
    }
}
