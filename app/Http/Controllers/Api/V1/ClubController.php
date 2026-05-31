<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreClubRequest;
use App\Http\Requests\Api\V1\UpdateClubRequest;
use App\Models\Club;
use App\Models\ClubMembership;
use App\Models\ClubMembershipRole;
use App\Services\ClubPermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClubController extends Controller
{
    public function __construct(private readonly ClubPermissionService $clubPermissions)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $clubs = Club::query()
            ->whereHas('memberships', fn ($q) => $q->where('user_id', $user->getKey()))
            ->with(['memberships' => fn ($q) => $q->where('user_id', $user->getKey())->with('roles')])
            ->orderBy('name')
            ->paginate(15);

        return response()->json($clubs);
    }

    public function store(StoreClubRequest $request): JsonResponse
    {
        $user = $request->user();

        $club = Club::query()->create([
            'name'               => $request->string('name')->toString(),
            'slug'               => Str::slug($request->string('name')->toString()) . '-' . Str::lower(Str::random(6)),
            'description'        => $request->input('description'),
            'created_by_user_id' => $user->getKey(),
        ]);

        $this->clubPermissions->addMembership($user, $club, [ClubMembershipRole::ROLE_CLUB_MANAGER]);

        return response()->json(
            $club->load(['memberships.user', 'memberships.roles']),
            201
        );
    }

    public function show(Club $club): JsonResponse
    {
        $this->authorize('view', $club);

        return response()->json(
            $club->load(['memberships.user', 'memberships.roles'])
        );
    }

    public function update(UpdateClubRequest $request, Club $club): JsonResponse
    {
        $this->authorize('update', $club);

        $club->update($request->validated());

        return response()->json($club);
    }
}
