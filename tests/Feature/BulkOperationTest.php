<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkOperationTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_status_change(): void
    {
        $user = User::factory()->create();
        $items = Item::factory()->count(2)->create(['status' => 'in_collection']);

        $this->actingAs($user)->post(route('items.bulk'), [
            'action' => 'change_status',
            'status' => 'damaged',
            'item_ids' => $items->pluck('id')->all(),
        ])->assertRedirect();

        foreach ($items as $item) {
            $this->assertSame('damaged', $item->fresh()->status);
        }
    }

    public function test_bulk_change_category(): void
    {
        $user = User::factory()->create();
        $items = Item::factory()->count(2)->create();
        $category = Category::factory()->create();

        $this->actingAs($user)->post(route('items.bulk'), [
            'action' => 'change_category',
            'category_id' => $category->id,
            'item_ids' => $items->pluck('id')->all(),
        ])->assertRedirect();

        foreach ($items as $item) {
            $this->assertSame($category->id, $item->fresh()->category_id);
        }
    }

    public function test_bulk_delete(): void
    {
        $user = User::factory()->create();
        $items = Item::factory()->count(2)->create();

        $this->actingAs($user)->post(route('items.bulk'), [
            'action' => 'delete',
            'item_ids' => $items->pluck('id')->all(),
        ])->assertRedirect();

        foreach ($items as $item) {
            $this->assertTrue($item->fresh()->is_deleted);
        }
    }

    public function test_bulk_validation_rejects_unknown_action_and_empty_selection(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('items.bulk'), [
            'action' => 'nuke_everything',
            'item_ids' => [],
        ])->assertSessionHasErrors(['action', 'item_ids']);
    }
}
