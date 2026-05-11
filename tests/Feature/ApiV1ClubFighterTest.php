<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiV1ClubFighterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_club_and_owner_membership(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/clubs', [
            'name' => 'Fight Club North',
            'description' => 'Initial club setup',
        ]);

        $response->assertCreated()->assertJsonPath('name', 'Fight Club North');

        $club = Club::query()->firstOrFail();
        $this->assertDatabaseHas('club_user', [
            'club_id' => $club->id,
            'user_id' => $user->id,
            'role' => 'owner',
        ]);
    }

    public function test_coach_can_create_fighter_in_own_club(): void
    {
        $user = User::factory()->create();
        $club = Club::query()->create([
            'name' => 'Riverside Club',
            'slug' => 'riverside-club',
            'created_by_user_id' => $user->id,
        ]);
        $club->users()->attach($user->id, ['role' => 'coach', 'joined_at' => now()]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/fighters', [
            'club_id' => $club->id,
            'first_name' => 'Anna',
            'last_name' => 'Meyer',
            'status' => 'active',
        ]);

        $response->assertCreated()->assertJsonPath('club.id', $club->id);
    }

    public function test_member_cannot_create_fighter(): void
    {
        $user = User::factory()->create();
        $club = Club::query()->create([
            'name' => 'Riverside Club',
            'slug' => 'riverside-club-member',
            'created_by_user_id' => $user->id,
        ]);
        $club->users()->attach($user->id, ['role' => 'member', 'joined_at' => now()]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/fighters', [
            'club_id' => $club->id,
            'first_name' => 'Ben',
            'last_name' => 'Klein',
        ]);

        $response->assertForbidden();
    }
}
