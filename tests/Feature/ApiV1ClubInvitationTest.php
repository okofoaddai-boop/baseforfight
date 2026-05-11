<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiV1ClubInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_invite_and_user_can_accept(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.test']);
        $invitee = User::factory()->create(['email' => 'join@example.test']);

        $club = Club::query()->create([
            'name' => 'Invite Club',
            'slug' => 'invite-club',
            'created_by_user_id' => $admin->getKey(),
        ]);
        $club->users()->attach($admin->getKey(), ['role' => 'admin', 'joined_at' => now()]);

        Sanctum::actingAs($admin);
        $inviteResponse = $this->postJson('/api/v1/clubs/' . $club->getKey() . '/invitations', [
            'email' => 'join@example.test',
            'role' => 'member',
        ]);

        $inviteResponse->assertCreated();
        $token = (string) $inviteResponse->json('token');

        Sanctum::actingAs($invitee);
        $acceptResponse = $this->postJson('/api/v1/clubs/invitations/accept', [
            'token' => $token,
        ]);

        $acceptResponse->assertOk();

        $this->assertDatabaseHas('club_user', [
            'club_id' => $club->getKey(),
            'user_id' => $invitee->getKey(),
            'role' => 'member',
        ]);
    }
}
