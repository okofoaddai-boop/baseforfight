<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CancelEventRequest;
use App\Http\Requests\Api\V1\StoreEventRequest;
use App\Http\Requests\Api\V1\UpdateEventRequest;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $clubIds = $request->user()->clubs()->pluck('clubs.id');

        $events = Event::query()
            ->whereIn('organizer_club_id', $clubIds)
            ->orderByDesc('starts_at')
            ->paginate(20);

        return response()->json($events);
    }

    public function store(StoreEventRequest $request): JsonResponse
    {
        $clubId = $request->integer('organizer_club_id');
        $this->authorize('createForClub', [Event::class, $clubId]);

        $status = $request->input('status', 'draft');

        $event = Event::query()->create([
            ...$request->validated(),
            'created_by_user_id' => $request->user()->getKey(),
            'status' => $status,
            'published_at' => $status === 'published' ? now() : null,
        ]);

        return response()->json($event, 201);
    }

    public function show(Event $event): JsonResponse
    {
        $this->authorize('view', $event);

        return response()->json($event);
    }

    public function update(UpdateEventRequest $request, Event $event): JsonResponse
    {
        $this->authorize('update', $event);

        $payload = $request->validated();

        if (array_key_exists('status', $payload)) {
            if ($payload['status'] === 'published' && $event->getAttribute('published_at') === null) {
                $payload['published_at'] = now();
            }

            if ($payload['status'] !== 'published') {
                $payload['published_at'] = null;
            }
        }

        $event->update($payload);

        return response()->json($event);
    }

    public function cancel(CancelEventRequest $request, Event $event): JsonResponse
    {
        $this->authorize('cancel', $event);

        $event->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancel_reason' => $request->string('cancel_reason')->toString(),
        ]);

        return response()->json($event);
    }
}
