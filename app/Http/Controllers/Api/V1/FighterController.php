<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreFighterRequest;
use App\Http\Requests\Api\V1\UpdateFighterRequest;
use App\Models\Fighter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FighterController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $clubIds = $request->user()->clubs()->pluck('clubs.id');

        $fighters = Fighter::with('club')
            ->whereIn('club_id', $clubIds)
            ->orderBy('last_name')
            ->paginate(20);

        return response()->json($fighters);
    }

    public function store(StoreFighterRequest $request): JsonResponse
    {
        $club = $request->resolveClub();

        $this->authorize('createForClub', [Fighter::class, $club->getKey()]);

        $fighter = Fighter::query()->create([
            ...$request->safe()->except(['club_id']),
            'club_id' => $club->getKey(),
            'created_by_user_id' => $request->user()->id,
        ]);

        return response()->json($fighter->load('club'), 201);
    }

    public function show(Fighter $fighter): JsonResponse
    {
        $this->authorize('view', $fighter);

        return response()->json($fighter->load('club'));
    }

    public function update(UpdateFighterRequest $request, Fighter $fighter): JsonResponse
    {
        $this->authorize('update', $fighter);

        $fighter->update($request->validated());

        return response()->json($fighter->load('club'));
    }
}
