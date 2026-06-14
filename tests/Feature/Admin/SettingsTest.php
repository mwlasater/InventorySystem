<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\Item;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_setting_get_returns_typed_default_then_stored_value(): void
    {
        $this->assertSame(90, Setting::get('trash_retention_days'));
        $this->assertIsBool(Setting::get('enforce_2fa_for_admins'));

        Setting::set('trash_retention_days', 30);
        Setting::set('enforce_2fa_for_admins', true);

        $this->assertSame(30, Setting::get('trash_retention_days'));
        $this->assertTrue(Setting::get('enforce_2fa_for_admins'));
    }

    public function test_settings_page_is_admin_only(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.settings.index'))
            ->assertForbidden();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('System');
    }

    public function test_admin_can_update_settings(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'app_display_name' => 'My Collection',
            'items_per_page' => 50,
            'trash_retention_days' => 45,
            'enforce_2fa_for_admins' => '1',
        ])->assertRedirect();

        $this->assertSame('My Collection', Setting::get('app_display_name'));
        $this->assertSame(50, Setting::get('items_per_page'));
        $this->assertSame(45, Setting::get('trash_retention_days'));
        $this->assertTrue(Setting::get('enforce_2fa_for_admins'));
    }

    public function test_unchecked_enforce_2fa_saves_as_false(): void
    {
        // The admin needs 2FA enabled, otherwise enforcement (which we turn on
        // below) would bounce them to 2FA setup before they reach settings.
        $secret = app(\App\Services\TwoFactorAuthService::class)->generateSecretKey();
        $admin = User::factory()->admin()->withTwoFactor($secret)->create();
        Setting::set('enforce_2fa_for_admins', true);

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'app_display_name' => 'Inv',
            'items_per_page' => 25,
            'trash_retention_days' => 90,
            // enforce_2fa_for_admins omitted -> hidden field sends '0'
            'enforce_2fa_for_admins' => '0',
        ])->assertRedirect();

        $this->assertFalse(Setting::get('enforce_2fa_for_admins'));
    }

    public function test_items_per_page_setting_drives_pagination(): void
    {
        Item::factory()->count(30)->create();
        Setting::set('items_per_page', 25);

        $this->actingAs(User::factory()->create())
            ->get(route('items.index'))
            ->assertOk()
            ->assertViewHas('items', fn ($items) => $items->perPage() === 25);

        Setting::set('items_per_page', 50);

        $this->actingAs(User::factory()->create())
            ->get(route('items.index'))
            ->assertViewHas('items', fn ($items) => $items->perPage() === 50);
    }

    public function test_trash_retention_setting_drives_purge(): void
    {
        Setting::set('trash_retention_days', 1);
        $item = Item::factory()->create(['is_deleted' => true, 'deleted_at' => now()->subDays(3)]);

        $this->artisan('trash:purge')->assertSuccessful();

        $this->assertDatabaseMissing('items', ['id' => $item->id]);
    }

    public function test_enforce_2fa_setting_drives_admin_redirect(): void
    {
        Setting::set('enforce_2fa_for_admins', true);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('profile.edit'))
            ->assertRedirect(route('two-factor.show'));
    }

    public function test_activity_log_renders_settings_audit_without_dangling_hash(): void
    {
        $admin = User::factory()->admin()->create();
        // App-level event with no entity_id (the nullable column at work).
        AuditLog::record('update', 'settings', null, null, ['items_per_page' => 50]);

        $this->actingAs($admin)->get(route('admin.activity-log'))
            ->assertOk()
            ->assertDontSee('settings #');
    }
}
