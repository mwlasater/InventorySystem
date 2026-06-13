<?php

namespace Tests\Unit\Services;

use App\Models\Item;
use App\Models\Location;
use App\Services\ItemFilterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ItemFilterServiceTest extends TestCase
{
    use RefreshDatabase;

    private ItemFilterService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ItemFilterService();
    }

    /** @param array<string, mixed> $params */
    private function filtered(array $params): array
    {
        $request = Request::create('/items', 'GET', $params);

        return $this->service->apply(Item::active(), $request)->pluck('id')->all();
    }

    public function test_filters_by_status(): void
    {
        $inCollection = Item::factory()->bare()->create(['status' => 'in_collection']);
        Item::factory()->bare()->create(['status' => 'damaged']);

        $this->assertEqualsCanonicalizing([$inCollection->id], $this->filtered(['status' => 'in_collection']));
    }

    public function test_filters_by_value_range(): void
    {
        $cheap = Item::factory()->bare()->create(['estimated_value' => 50]);
        $pricey = Item::factory()->bare()->create(['estimated_value' => 500]);

        $this->assertEqualsCanonicalizing([$pricey->id], $this->filtered(['value_min' => 100]));
        $this->assertEqualsCanonicalizing([$cheap->id], $this->filtered(['value_max' => 100]));
    }

    public function test_filters_by_brand_partial_match(): void
    {
        $sony = Item::factory()->bare()->create(['brand' => 'Sony Electronics']);
        Item::factory()->bare()->create(['brand' => 'Panasonic']);

        $this->assertEqualsCanonicalizing([$sony->id], $this->filtered(['brand' => 'Sony']));
    }

    public function test_filters_by_favorites_only(): void
    {
        $favorite = Item::factory()->bare()->favorite()->create();
        Item::factory()->bare()->create();

        $this->assertEqualsCanonicalizing([$favorite->id], $this->filtered(['favorites' => '1']));
    }

    public function test_location_filter_includes_descendants(): void
    {
        $building = Location::factory()->level('building')->create();
        $shelf = Location::factory()->level('shelf')->create(['parent_id' => $building->id]);

        $onBuilding = Item::factory()->bare()->create(['location_id' => $building->id]);
        $onShelf = Item::factory()->bare()->create(['location_id' => $shelf->id]);
        Item::factory()->bare()->create(['location_id' => Location::factory()->create()->id]);

        $this->assertEqualsCanonicalizing(
            [$onBuilding->id, $onShelf->id],
            $this->filtered(['location_id' => $building->id]),
        );
    }

    public function test_apply_sorting_orders_by_whitelisted_field(): void
    {
        Item::factory()->bare()->create(['name' => 'Banana']);
        Item::factory()->bare()->create(['name' => 'Apple']);
        Item::factory()->bare()->create(['name' => 'Cherry']);

        $request = Request::create('/items', 'GET', ['sort' => 'name', 'dir' => 'asc']);
        $names = $this->service->applySorting(Item::active(), $request)->pluck('name')->all();

        $this->assertSame(['Apple', 'Banana', 'Cherry'], $names);
    }

    public function test_apply_sorting_falls_back_for_unknown_field(): void
    {
        Item::factory()->bare()->create();
        Item::factory()->bare()->create();

        $request = Request::create('/items', 'GET', ['sort' => 'drop table', 'dir' => 'sideways']);

        // Should not throw and should return all rows (falls back to created_at desc).
        $ids = $this->service->applySorting(Item::active(), $request)->pluck('id')->all();
        $this->assertCount(2, $ids);
    }
}
