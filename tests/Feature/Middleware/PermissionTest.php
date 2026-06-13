<?php

namespace Tests\Feature\Middleware;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_routes_are_forbidden_to_regular_users(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_admin_routes_are_accessible_to_admins(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.users.index'))
            ->assertOk();
    }

    public function test_deactivated_user_is_logged_out_by_middleware(): void
    {
        $user = User::factory()->inactive()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_user_requiring_password_change_is_forced_to_change_page(): void
    {
        $user = User::factory()->mustChangePassword()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('password.force-change'));
    }

    public function test_admin_can_create_a_user(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'role' => 'user',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'username' => 'newuser',
            'force_password_change' => true,
        ]);
    }
}
