<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\TwoFactorAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorTest extends TestCase
{
    use RefreshDatabase;

    private function secret(): string
    {
        return app(TwoFactorAuthService::class)->generateSecretKey();
    }

    private function otp(string $secret): string
    {
        return app(Google2FA::class)->getCurrentOtp($secret);
    }

    // --- Enrollment / management ---------------------------------------------

    public function test_enable_generates_unconfirmed_secret_and_recovery_codes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('two-factor.enable'))
            ->assertRedirect(route('two-factor.show'));

        $user->refresh();
        $this->assertNotNull($user->two_factor_secret);
        $this->assertCount(8, $user->two_factor_recovery_codes);
        $this->assertFalse($user->hasTwoFactorEnabled(), 'Must stay disabled until a code is confirmed');
    }

    public function test_management_page_renders_qr_during_enrollment(): void
    {
        $user = User::factory()->create([
            'two_factor_secret' => $this->secret(),
            'two_factor_recovery_codes' => ['AAAAA-BBBBB'],
        ]);

        $this->actingAs($user)->get(route('two-factor.show'))
            ->assertOk()
            ->assertSee('<svg', false);
    }

    public function test_confirming_a_valid_code_enables_two_factor(): void
    {
        $secret = $this->secret();
        $user = User::factory()->create([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => ['AAAAA-BBBBB'],
        ]);

        $this->actingAs($user)->post(route('two-factor.confirm'), ['code' => $this->otp($secret)])
            ->assertRedirect(route('two-factor.show'));

        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());
        $this->assertDatabaseHas('audit_log', ['action' => 'two_factor_enabled', 'entity_id' => $user->id]);
    }

    public function test_confirming_an_invalid_code_does_not_enable(): void
    {
        $user = User::factory()->create([
            'two_factor_secret' => $this->secret(),
            'two_factor_recovery_codes' => ['AAAAA-BBBBB'],
        ]);

        $this->actingAs($user)->post(route('two-factor.confirm'), ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());
    }

    public function test_disable_requires_correct_password(): void
    {
        $user = User::factory()->password('Secret123!')->withTwoFactor($this->secret())->create();

        $this->actingAs($user)->delete(route('two-factor.disable'), ['password' => 'wrong'])
            ->assertSessionHasErrors('password');
        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());

        $this->actingAs($user)->delete(route('two-factor.disable'), ['password' => 'Secret123!'])
            ->assertRedirect(route('two-factor.show'));
        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());
        $this->assertNull($user->fresh()->two_factor_secret);
    }

    public function test_recovery_codes_can_be_regenerated(): void
    {
        $user = User::factory()->withTwoFactor($this->secret(), ['OLD11-OLD22'])->create();

        $this->actingAs($user)->post(route('two-factor.recovery-codes'))
            ->assertRedirect(route('two-factor.show'));

        $codes = $user->fresh()->two_factor_recovery_codes;
        $this->assertCount(8, $codes);
        $this->assertNotContains('OLD11-OLD22', $codes);
    }

    // --- Login challenge ------------------------------------------------------

    public function test_two_factor_user_is_challenged_after_password(): void
    {
        $user = User::factory()->password('Secret123!')->withTwoFactor($this->secret())
            ->create(['username' => 'jdoe']);

        $this->post('/login', ['username' => 'jdoe', 'password' => 'Secret123!'])
            ->assertRedirect(route('two-factor.challenge'));

        $this->assertGuest();
        $this->assertEquals($user->id, session('auth.2fa.user_id'));
    }

    public function test_challenge_with_valid_code_completes_login(): void
    {
        $secret = $this->secret();
        $user = User::factory()->password('Secret123!')->withTwoFactor($secret)
            ->create(['username' => 'jdoe']);

        $this->post('/login', ['username' => 'jdoe', 'password' => 'Secret123!']);
        $this->post(route('two-factor.challenge.store'), ['code' => $this->otp($secret)])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user->fresh());
        $this->assertNull(session('auth.2fa.user_id'));
    }

    public function test_challenge_with_invalid_code_is_rejected(): void
    {
        $user = User::factory()->password('Secret123!')->withTwoFactor($this->secret())
            ->create(['username' => 'jdoe']);

        $this->post('/login', ['username' => 'jdoe', 'password' => 'Secret123!']);
        $this->post(route('two-factor.challenge.store'), ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->assertGuest();
        $this->assertDatabaseHas('audit_log', ['action' => 'two_factor_failed', 'entity_id' => $user->id]);
    }

    public function test_recovery_code_completes_login_and_is_consumed(): void
    {
        $user = User::factory()->password('Secret123!')
            ->withTwoFactor($this->secret(), ['RESCU-CODE1', 'RESCU-CODE2'])
            ->create(['username' => 'jdoe']);

        $this->post('/login', ['username' => 'jdoe', 'password' => 'Secret123!']);
        $this->post(route('two-factor.challenge.store'), ['recovery_code' => 'RESCU-CODE1'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user->fresh());
        $this->assertNotContains('RESCU-CODE1', $user->fresh()->two_factor_recovery_codes);
    }

    public function test_user_with_unconfirmed_secret_logs_in_normally(): void
    {
        // Secret present but never confirmed => 2FA is not active; no challenge.
        $user = User::factory()->password('Secret123!')
            ->create(['username' => 'jdoe', 'two_factor_secret' => $this->secret()]);

        $this->post('/login', ['username' => 'jdoe', 'password' => 'Secret123!'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user->fresh());
    }

    // --- Admin enforcement ----------------------------------------------------

    public function test_admin_without_two_factor_is_forced_to_setup_when_enforced(): void
    {
        config(['auth.two_factor.enforce_for_admins' => true]);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertRedirect(route('two-factor.show'));
    }

    public function test_enforcement_off_by_default_lets_admin_proceed(): void
    {
        $admin = User::factory()->admin()->create();

        // A protected page in the same middleware group returns 200 (not a 302
        // bounce to 2FA setup) when enforcement is off.
        $this->actingAs($admin)->get(route('profile.edit'))->assertOk();
    }
}
