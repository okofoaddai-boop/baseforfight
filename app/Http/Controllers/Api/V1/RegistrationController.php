<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreRegistrationRequest;
use App\Http\Requests\Api\V1\UpdateRegistrationRequest;
use App\Models\Event;
use App\Models\Fighter;
use App\Models\Registration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $clubIds = $request->user()->clubs()->pluck('clubs.id');

        $registrations = Registration::query()
            ->whereHas('fighter', fn ($query) => $query->whereIn('club_id', $clubIds))
            ->with(['event', 'fighter'])
            ->orderByDesc('id')
            ->paginate(20);

        return response()->json($registrations);
    }

    public function store(StoreRegistrationRequest $request): JsonResponse
    {
        /** @var Fighter $fighter */
        $fighter = Fighter::query()->findOrFail($request->integer('fighter_id'));
        /** @var Event $event */
        $event = Event::query()->findOrFail($request->integer('event_id'));

        $clubId = (int) $fighter->getAttribute('club_id');
        $this->authorize('createForFighterClub', [Registration::class, $clubId]);

        if ($fighter->getAttribute('status') !== 'active') {
            return response()->json(['message' => 'Only active fighters can be registered.'], 422);
        }

        if ($event->getAttribute('status') !== 'published' || $event->getAttribute('cancelled_at') !== null) {
            return response()->json(['message' => 'Registrations are only allowed for published events.'], 422);
        }

        $deadline = $event->getAttribute('registration_deadline');
        if ($deadline && now()->greaterThan($deadline)) {
            return response()->json(['message' => 'Registration deadline has passed.'], 422);
        }

        $maxRegistrations = $event->getAttribute('max_registrations');
        $registrationCount = Registration::query()
            ->where('event_id', $event->getKey())
            ->count();

        if (is_numeric($maxRegistrations) && $registrationCount >= (int) $maxRegistrations) {
            if (! $event->getAttribute('allow_waitlist')) {
                return response()->json(['message' => 'Event registration limit reached.'], 422);
            }
        }

        if (Registration::query()->where('fighter_id', $fighter->getKey())->where('event_id', $event->getKey())->exists()) {
            return response()->json(['message' => 'Fighter is already registered for this event.'], 422);
        }

        $registration = Registration::query()->create([
            'fighter_id' => $fighter->getKey(),
            'event_id' => $event->getKey(),
            'status' => is_numeric($maxRegistrations) && $registrationCount >= (int) $maxRegistrations ? 'waitlisted' : 'pending',
            'registered_by_user_id' => $request->user()->getKey(),
            'notes' => $request->input('notes'),
        ]);

        return response()->json($registration->load(['event', 'fighter']), 201);
    }

    public function show(Registration $registration): JsonResponse
    {
        $this->authorize('view', $registration);

        return response()->json($registration->load(['event', 'fighter']));
    }

    public function update(UpdateRegistrationRequest $request, Registration $registration): JsonResponse
    {
        $this->authorize('update', $registration);

        $registration->update($request->validated());

        return response()->json($registration->load(['event', 'fighter']));
    }

    public function destroy(Registration $registration): JsonResponse
    {
        $this->authorize('delete', $registration);

        $registration->delete();

        return response()->json([], 204);
    }
}
