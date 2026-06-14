<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_for_authenticated_user(): void
    {
        $category = Category::factory()->create();
        Item::factory()->count(3)->create(['category_id' => $category->id]);

        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('itemsByCategory');
    }

    public function test_categories_with_no_active_items_are_excluded(): void
    {
        $withItems = Category::factory()->create(['name' => 'Populated']);
        Item::factory()->create(['category_id' => $withItems->id]);
        $empty = Category::factory()->create(['name' => 'Empty']);

        $names = $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->viewData('itemsByCategory')
            ->pluck('name');

        $this->assertTrue($names->contains('Populated'));
        $this->assertFalse($names->contains('Empty'), 'Empty categories should be excluded from the breakdown');
    }

    public function test_sidebar_links_point_to_real_routes(): void
    {
        // Regression: the sidebar nav array previously hardcoded every item to
        // route('dashboard'). Each item must resolve to its own destination.
        $response = $this->actingAs(User::factory()->admin()->create())
            ->get(route('dashboard'))
            ->assertOk();

        foreach (['items.index', 'items.create', 'locations.index', 'categories.index',
            'reports.index', 'admin.users.index', 'admin.settings.index'] as $name) {
            $response->assertSee(route($name), false);
        }
    }

    public function test_chart_data_endpoint_returns_category_breakdowns(): void
    {
        $category = Category::factory()->create(['name' => 'Tools']);
        Item::factory()->count(2)->create([
            'category_id' => $category->id,
            'estimated_value' => 50,
        ]);

        $response = $this->actingAs(User::factory()->create())
            ->getJson(route('api.dashboard.charts'));

        $response->assertOk()
            ->assertJsonStructure(['itemsByCategory', 'valueByCategory']);
        $this->assertSame('Tools', $response->json('itemsByCategory.0.label'));
    }
}
