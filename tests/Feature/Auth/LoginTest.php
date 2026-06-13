<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_is_reachable_by_guests(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_user_can_log_in_with_valid_credentials(): void
    {
        $user = User::factory()->password('Secret123!')->create(['username' => 'jdoe']);

        $response = $this->post('/login', [
            'username' => 'jdoe',
            'password' => 'Secret123!',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
        $this->assertDatabaseHas('audit_log', ['action' => 'login', 'entity_id' => $user->id]);
    }

    public function test_invalid_password_increments_failed_attempts_and_is_audited(): void
    {
        $user = User::factory()->password('Secret123!')->create(['username' => 'jdoe']);

        $this->post('/login', ['username' => 'jdoe', 'password' => 'wrong'])
            ->assertSessionHasErrors('username');

        $this->assertGuest();
        $this->assertSame(1, $user->fresh()->failed_login_attempts);
        $this->assertDatabaseHas('audit_log', ['action' => 'failed_login', 'entity_id' => $user->id]);
    }

    public function test_account_locks_after_five_failed_attempts(): void
    {
        $user = User::factory()->password('Secret123!')->create(['username' => 'jdoe']);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['username' => 'jdoe', 'password' => 'wrong']);
        }

        $this->assertTrue($user->fresh()->isLocked());

        // Even the correct password is rejected while locked.
        $this->post('/login', ['username' => 'jdoe', 'password' => 'Secret123!'])
            ->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_too_many_attempts(): void
    {
        // Unknown username: no account lockout is possible, so this isolates
        // the IP+username rate limiter from the per-account lockout.
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['username' => 'ghost', 'password' => 'wrong']);
        }

        $response = $this->post('/login', ['username' => 'ghost', 'password' => 'wrong']);

        $message = session('errors')->getBag('default')->first('username');
        $this->assertStringContainsString('Too many login attempts', $message);
    }

    public function test_rate_limit_is_keyed_per_username(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['username' => 'ghost', 'password' => 'wrong']);
        }

        // A different username from the same IP is unaffected by ghost's throttle.
        $user = User::factory()->password('Secret123!')->create(['username' => 'jdoe']);
        $this->post('/login', ['username' => 'jdoe', 'password' => 'Secret123!'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_deactivated_user_cannot_log_in(): void
    {
        User::factory()->inactive()->password('Secret123!')->create(['username' => 'jdoe']);

        $this->post('/login', ['username' => 'jdoe', 'password' => 'Secret123!'])
            ->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_user_requiring_password_change_is_redirected(): void
    {
        User::factory()->mustChangePassword()->password('Secret123!')->create(['username' => 'jdoe']);

        $this->post('/login', ['username' => 'jdoe', 'password' => 'Secret123!'])
            ->assertRedirect(route('password.force-change'));
    }

    public function test_user_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertDatabaseHas('audit_log', ['action' => 'logout', 'entity_id' => $user->id]);
    }
}
