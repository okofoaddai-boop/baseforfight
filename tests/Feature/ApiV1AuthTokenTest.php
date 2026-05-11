<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiV1AuthTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_issue_token_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'api@example.test',
            'password' => bcrypt('secret1234'),
        ]);

        $response = $this->postJson('/api/v1/auth/token', [
            'email' => 'api@example.test',
            'password' => 'secret1234',
            'device_name' => 'phpunit',
            'abilities' => ['fighters:read'],
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure(['token_type', 'access_token', 'user' => ['id', 'email']]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
        ]);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        User::factory()->create([
            'email' => 'api@example.test',
            'password' => bcrypt('secret1234'),
        ]);

        $response = $this->postJson('/api/v1/auth/token', [
            'email' => 'api@example.test',
            'password' => 'wrong',
            'device_name' => 'phpunit',
        ]);

        $response->assertStatus(422);
    }
}
