<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Item;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ItemApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Sanctum::actingAs(User::factory()->create());
    }

    public function test_items_index_returns_active_items_paginated(): void
    {
        Item::factory()->count(3)->create();
        Item::factory()->create(['is_deleted' => true, 'deleted_at' => now()]);

        $this->getJson('/api/v1/items')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure(['data' => [['id', 'name', 'status_label']], 'meta', 'links']);
    }

    public function test_items_index_filters_by_category(): void
    {
        $category = Category::factory()->create();
        Item::factory()->count(2)->create(['category_id' => $category->id]);
        Item::factory()->create();

        $this->getJson('/api/v1/items?category_id='.$category->id)
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_item_show_returns_the_item_with_relations(): void
    {
        $item = Item::factory()->create();
        $item->tags()->attach(Tag::findOrCreateByName('Vintage'));

        $this->getJson('/api/v1/items/'.$item->id)
            ->assertOk()
            ->assertJsonPath('data.id', $item->id)
            ->assertJsonPath('data.tags', ['Vintage']);
    }

    public function test_item_show_404s_for_trashed_item(): void
    {
        $item = Item::factory()->create(['is_deleted' => true, 'deleted_at' => now()]);

        $this->getJson('/api/v1/items/'.$item->id)->assertNotFound();
    }

    public function test_search_finds_items_by_name(): void
    {
        Item::factory()->create(['name' => 'Vintage Camera']);
        Item::factory()->create(['name' => 'Garden Hose']);

        $this->getJson('/api/v1/search?q=camera')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Vintage Camera');
    }

    public function test_barcode_lookup_returns_match_or_404(): void
    {
        $item = Item::factory()->create(['barcode' => '012345678905']);

        $this->getJson('/api/v1/barcode-lookup?barcode=012345678905')
            ->assertOk()
            ->assertJsonPath('data.id', $item->id);

        $this->getJson('/api/v1/barcode-lookup?barcode=000000000000')->assertNotFound();
    }

    public function test_tags_index_lists_tags(): void
    {
        Tag::findOrCreateByName('Fragile');
        Tag::findOrCreateByName('Electronics');

        $this->getJson('/api/v1/tags')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }
}
