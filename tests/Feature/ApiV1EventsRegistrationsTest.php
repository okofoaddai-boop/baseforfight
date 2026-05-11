<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Event;
use App\Models\Fighter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiV1EventsRegistrationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_cannot_create_event(): void
    {
        $user = User::factory()->create();
        $club = Club::query()->create([
            'name' => 'Event Club',
            'slug' => 'event-club',
            'created_by_user_id' => $user->getKey(),
        ]);
        $club->users()->attach($user->getKey(), ['role' => 'member', 'joined_at' => now()]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/events', [
            'title' => 'Open Cup',
            'starts_at' => now()->addDays(10)->toDateTimeString(),
            'organizer_club_id' => $club->getKey(),
        ]);

        $response->assertForbidden();
    }

    public function test_admin_can_create_event_and_coach_can_register_fighter(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create();
        $coach = User::factory()->create();

        $club = Club::query()->create([
            'name' => 'North Club',
            'slug' => 'north-club',
            'created_by_user_id' => $admin->getKey(),
        ]);
        $club->users()->attach($admin->getKey(), ['role' => 'admin', 'joined_at' => now()]);
        $club->users()->attach($coach->getKey(), ['role' => 'coach', 'joined_at' => now()]);

        $fighter = Fighter::query()->create([
            'club_id' => $club->getKey(),
            'created_by_user_id' => $coach->getKey(),
            'first_name' => 'Lena',
            'last_name' => 'Fischer',
            'status' => 'active',
        ]);

        Sanctum::actingAs($admin);
        $eventResponse = $this->postJson('/api/v1/events', [
            'title' => 'Open Cup',
            'starts_at' => now()->addDays(10)->toDateTimeString(),
            'registration_deadline' => now()->addDays(5)->toDateTimeString(),
            'max_registrations' => 5,
            'status' => 'published',
            'organizer_club_id' => $club->getKey(),
        ]);

        $eventResponse->assertCreated();
        $eventId = (int) $eventResponse->json('id');

        Sanctum::actingAs($coach);
        $registrationResponse = $this->postJson('/api/v1/registrations', [
            'fighter_id' => $fighter->getKey(),
            'event_id' => $eventId,
        ]);

        $registrationResponse->assertCreated();
        $this->assertDatabaseHas('registrations', [
            'fighter_id' => $fighter->getKey(),
            'event_id' => $eventId,
        ]);
    }

    public function test_registration_fails_for_draft_event(): void
    {
        $user = User::factory()->create();

        $club = Club::query()->create([
            'name' => 'Draft Club',
            'slug' => 'draft-club',
            'created_by_user_id' => $user->getKey(),
        ]);
        $club->users()->attach($user->getKey(), ['role' => 'coach', 'joined_at' => now()]);

        $fighter = Fighter::query()->create([
            'club_id' => $club->getKey(),
            'created_by_user_id' => $user->getKey(),
            'first_name' => 'Mia',
            'last_name' => 'Brandt',
            'status' => 'active',
        ]);

        $event = Event::query()->create([
            'title' => 'Draft Event',
            'starts_at' => now()->addDays(10),
            'status' => 'draft',
            'organizer_club_id' => $club->getKey(),
            'created_by_user_id' => $user->getKey(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/registrations', [
            'fighter_id' => $fighter->getKey(),
            'event_id' => $event->getKey(),
        ]);

        $response->assertStatus(422);
    }
}
