<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreRegistrationRequest;
use App\Http\Requests\Api\V1\UpdateRegistrationRequest;
use App\Models\Event;
use App\Models\Fighter;
use App\Models\Registration;
use App\Services\RegistrationWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    public function __construct(
        private readonly RegistrationWorkflowService $registrationWorkflow,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $clubIds = $request->user()->memberships()->pluck('club_id');

        $registrations = Registration::query()
            ->where(function ($query) use ($clubIds, $request): void {
                $query->whereHas('fighter', fn ($fighterQuery) => $fighterQuery->whereIn('club_id', $clubIds))
                    ->orWhereHas('event', fn ($eventQuery) => $eventQuery->whereIn('organizer_club_id', $clubIds));
            })
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

        if ($event->starts_at !== null && now()->greaterThan($event->starts_at)) {
            return response()->json(['message' => 'Registrations are no longer possible for ended events.'], 422);
        }

        if (Registration::query()->where('fighter_id', $fighter->getKey())->where('event_id', $event->getKey())->exists()) {
            return response()->json(['message' => 'Fighter is already registered for this event.'], 422);
        }

        $this->registrationWorkflow->lockBillingForEvent($event);
        $event->refresh();

        $targetStatus = $this->registrationWorkflow->determineInitialStatus(
            $event,
            $this->registrationWorkflow->activeRegistrationCount($event)
        );

        if ($targetStatus === null) {
            return response()->json(['message' => 'Event registration limit reached.'], 422);
        }

        $registration = Registration::query()->create([
            'fighter_id' => $fighter->getKey(),
            'event_id' => $event->getKey(),
            'status' => $targetStatus,
            'registered_by_user_id' => $request->user()->getKey(),
            'notes' => $request->input('notes'),
            'status_changed_at' => now(),
        ]);

        $this->registrationWorkflow->markCreated($registration, $request->user(), 'api_registration_created', ['source' => 'api']);

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

        if ($request->filled('notes')) {
            $registration->notes = $request->string('notes')->toString();
            $registration->save();
        }

        $this->registrationWorkflow->transitionStatus(
            $registration,
            $request->string('status')->toString(),
            $request->user(),
            'api_status_update',
            ['source' => 'api']
        );

        return response()->json($registration->load(['event', 'fighter']));
    }

    public function destroy(Registration $registration): JsonResponse
    {
        $this->authorize('delete', $registration);

        $this->registrationWorkflow->transitionStatus(
            $registration,
            Registration::STATUS_WITHDRAWN,
            request()->user(),
            'api_withdraw',
            ['source' => 'api']
        );

        return response()->json([], 204);
    }
}
