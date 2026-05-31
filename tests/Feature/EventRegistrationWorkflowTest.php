<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\ClubMembership;
use App\Models\ClubMembershipRole;
use App\Models\Event;
use App\Models\Fighter;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class EventRegistrationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_manual_approval_before_deadline_creates_waiting_registration(): void
    {
        Carbon::setTestNow(now());

        [$trainer, $trainerClub, $fighter] = $this->makeTrainerWithFighter('trainer-before@test');
        [$organizer, $organizerClub] = $this->makeOrganizer('organizer-before@test');

        $event = Event::query()->create([
            'title' => 'Manual Approval Cup',
            'starts_at' => now()->addDays(10),
            'registration_deadline' => now()->addDays(3),
            'registration_approval_mode' => 'manual',
            'status' => 'published',
            'organizer_club_id' => $organizerClub->getKey(),
            'created_by_user_id' => $organizer->getKey(),
        ]);

        $response = $this->actingAs($trainer)->post(route('events.registrations.sync', $event), [
            'fighter_ids' => [$fighter->getKey()],
        ]);

        $response->assertRedirect(route('events.show', ['event' => $event, 'tab' => 'registrations']));

        $this->assertDatabaseHas('registrations', [
            'event_id' => $event->getKey(),
            'fighter_id' => $fighter->getKey(),
            'status' => Registration::STATUS_WAITING,
            'billable_at' => null,
        ]);
    }

    public function test_post_deadline_approval_makes_registration_billable(): void
    {
        Carbon::setTestNow(now());

        [$trainer, $trainerClub, $fighter] = $this->makeTrainerWithFighter('trainer-late@test');
        [$organizer, $organizerClub] = $this->makeOrganizer('organizer-late@test');

        $event = Event::query()->create([
            'title' => 'Late Entry Cup',
            'starts_at' => now()->addDays(5),
            'registration_deadline' => now()->subDay(),
            'registration_approval_mode' => 'auto',
            'status' => 'published',
            'organizer_club_id' => $organizerClub->getKey(),
            'created_by_user_id' => $organizer->getKey(),
        ]);

        $this->actingAs($trainer)->post(route('events.registrations.sync', $event), [
            'fighter_ids' => [$fighter->getKey()],
        ])->assertRedirect(route('events.show', ['event' => $event, 'tab' => 'registrations']));

        /** @var Registration $registration */
        $registration = Registration::query()->where('event_id', $event->getKey())->where('fighter_id', $fighter->getKey())->firstOrFail();

        $this->assertSame(Registration::STATUS_WAITING, $registration->status);
        $this->assertNull($registration->billable_at);

        $this->actingAs($organizer)->post(route('events.registrations.manage', $event), [
            'registration_ids' => [$registration->getKey()],
            'status' => Registration::STATUS_ACTIVE,
            'reason' => 'test_activation',
        ])->assertRedirect(route('events.show', ['event' => $event, 'tab' => 'registrations']));

        $registration->refresh();

        $this->assertSame(Registration::STATUS_ACTIVE, $registration->status);
        $this->assertNotNull($registration->billable_at);
        $this->assertSame(Registration::BILLABLE_REASON_POST_DEADLINE_APPROVAL, $registration->billable_reason);
    }

    public function test_withdrawal_after_deadline_keeps_locked_billable_registration(): void
    {
        Carbon::setTestNow(now());

        [$trainer, $trainerClub, $fighter] = $this->makeTrainerWithFighter('trainer-lock@test');
        [$organizer, $organizerClub] = $this->makeOrganizer('organizer-lock@test');

        $event = Event::query()->create([
            'title' => 'Locked Billing Cup',
            'starts_at' => now()->addDays(6),
            'registration_deadline' => now()->subDay(),
            'registration_approval_mode' => 'auto',
            'status' => 'published',
            'organizer_club_id' => $organizerClub->getKey(),
            'created_by_user_id' => $organizer->getKey(),
        ]);

        $registration = Registration::query()->create([
            'fighter_id' => $fighter->getKey(),
            'event_id' => $event->getKey(),
            'status' => Registration::STATUS_ACTIVE,
            'registered_by_user_id' => $trainer->getKey(),
            'status_changed_at' => now()->subDays(2),
        ]);

        $this->actingAs($organizer)->post(route('events.registrations.manage', $event), [
            'registration_ids' => [$registration->getKey()],
            'status' => Registration::STATUS_WITHDRAWN,
            'reason' => 'test_withdrawal',
        ])->assertRedirect(route('events.show', ['event' => $event, 'tab' => 'registrations']));

        $registration->refresh();
        $event->refresh();

        $this->assertSame(Registration::STATUS_WITHDRAWN, $registration->status);
        $this->assertNotNull($registration->billable_at);
        $this->assertSame(Registration::BILLABLE_REASON_DEADLINE_ACTIVE, $registration->billable_reason);
        $this->assertNotNull($event->billing_locked_at);
    }

    private function makeTrainerWithFighter(string $email): array
    {
        $trainer = User::factory()->create(['email' => $email]);
        $club = Club::query()->create([
            'name' => 'Trainer Club ' . $trainer->getKey(),
            'slug' => 'trainer-club-' . $trainer->getKey(),
            'created_by_user_id' => $trainer->getKey(),
        ]);

        $this->attachRole($trainer, $club, ClubMembershipRole::ROLE_TRAINER);

        $fighter = Fighter::query()->create([
            'club_id' => $club->getKey(),
            'created_by_user_id' => $trainer->getKey(),
            'first_name' => 'Lena',
            'last_name' => 'Fischer',
            'status' => 'active',
        ]);

        return [$trainer, $club, $fighter];
    }

    private function makeOrganizer(string $email): array
    {
        $organizer = User::factory()->create(['email' => $email]);
        $club = Club::query()->create([
            'name' => 'Organizer Club ' . $organizer->getKey(),
            'slug' => 'organizer-club-' . $organizer->getKey(),
            'created_by_user_id' => $organizer->getKey(),
        ]);

        $this->attachRole($organizer, $club, ClubMembershipRole::ROLE_EVENT_MANAGER);

        return [$organizer, $club];
    }

    private function attachRole(User $user, Club $club, string $role): void
    {
        $membership = ClubMembership::query()->create([
            'club_id' => $club->getKey(),
            'user_id' => $user->getKey(),
            'joined_at' => now(),
        ]);

        ClubMembershipRole::query()->create([
            'club_membership_id' => $membership->getKey(),
            'role' => $role,
        ]);
    }
}