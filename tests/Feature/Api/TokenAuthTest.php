<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TokenAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_rejects_unauthenticated_requests(): void
    {
        $this->getJson('/api/v1/user')->assertUnauthorized();
    }

    public function test_a_valid_token_authenticates(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/user')
            ->assertOk()
            ->assertJsonFragment(['id' => $user->id, 'username' => $user->username]);
    }

    public function test_a_revoked_token_no_longer_works(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $user->tokens()->delete();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/user')
            ->assertUnauthorized();
    }

    public function test_a_deactivated_users_token_is_forbidden(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $user->update(['is_active' => false]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/user')
            ->assertForbidden();
    }

    // --- Self-service token management (web) ---------------------------------

    public function test_user_can_create_a_token_and_sees_it_once(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('api-tokens.store'), ['name' => 'Mobile scanner'])
            ->assertRedirect(route('api-tokens.index'))
            ->assertSessionHas('plain_text_token');

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'Mobile scanner',
        ]);
    }

    public function test_user_can_revoke_their_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->accessToken;

        $this->actingAs($user)->delete(route('api-tokens.destroy', $token->id))
            ->assertRedirect(route('api-tokens.index'));

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->id]);
    }

    public function test_user_cannot_revoke_another_users_token(): void
    {
        $owner = User::factory()->create();
        $token = $owner->createToken('test')->accessToken;
        $attacker = User::factory()->create();

        $this->actingAs($attacker)->delete(route('api-tokens.destroy', $token->id))
            ->assertRedirect();

        $this->assertDatabaseHas('personal_access_tokens', ['id' => $token->id]);
    }
}
