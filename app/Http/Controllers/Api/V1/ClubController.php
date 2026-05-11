<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreClubRequest;
use App\Http\Requests\Api\V1\UpdateClubRequest;
use App\Models\Club;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClubController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $clubs = $request->user()
            ->clubs()
            ->orderBy('name')
            ->paginate(15);

        return response()->json($clubs);
    }

    public function store(StoreClubRequest $request): JsonResponse
    {
        $club = new Club([
            'name' => $request->string('name')->toString(),
            'slug' => Str::slug($request->string('name')->toString()) . '-' . Str::lower(Str::random(6)),
            'description' => $request->input('description'),
            'created_by_user_id' => $request->user()->id,
        ]);
        $club->save();

        $club->users()->attach($request->user()->id, [
            'role' => 'manager',
            'joined_at' => now(),
        ]);

        return response()->json($club->load('users'), 201);
    }

    public function show(Club $club): JsonResponse
    {
        $this->authorize('view', $club);

        return response()->json($club->load('users'));
    }

    public function update(UpdateClubRequest $request, Club $club): JsonResponse
    {
        $this->authorize('update', $club);

        $club->update($request->validated());

        return response()->json($club);
    }
}
