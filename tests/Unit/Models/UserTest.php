<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_admin(): void
    {
        $this->assertTrue(User::factory()->admin()->create()->isAdmin());
        $this->assertFalse(User::factory()->create()->isAdmin());
    }

    public function test_admin_has_every_permission(): void
    {
        $admin = User::factory()->admin()->create(['permissions' => []]);
        $this->assertTrue($admin->hasPermission('anything.at.all'));
    }

    public function test_regular_user_permission_is_list_based(): void
    {
        $user = User::factory()->create(['permissions' => ['items.create']]);

        $this->assertTrue($user->hasPermission('items.create'));
        $this->assertFalse($user->hasPermission('items.delete'));
    }

    public function test_is_locked_reflects_locked_until(): void
    {
        $this->assertTrue(User::factory()->create(['locked_until' => now()->addMinutes(5)])->isLocked());
        $this->assertFalse(User::factory()->create(['locked_until' => now()->subMinutes(5)])->isLocked());
        $this->assertFalse(User::factory()->create(['locked_until' => null])->isLocked());
    }

    public function test_account_locks_after_five_failed_attempts(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $user->incrementFailedAttempts();
        }

        $this->assertSame(5, $user->fresh()->failed_login_attempts);
        $this->assertTrue($user->fresh()->isLocked());
    }

    public function test_reset_failed_attempts_clears_lock(): void
    {
        $user = User::factory()->create([
            'failed_login_attempts' => 5,
            'locked_until' => now()->addMinutes(15),
        ]);

        $user->resetFailedAttempts();

        $this->assertSame(0, $user->fresh()->failed_login_attempts);
        $this->assertNull($user->fresh()->locked_until);
        $this->assertFalse($user->fresh()->isLocked());
    }
}
