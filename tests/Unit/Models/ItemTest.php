<?php

namespace Tests\Unit\Models;

use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_gain_loss_is_estimated_value_minus_purchase_price(): void
    {
        $item = Item::factory()->bare()->create([
            'estimated_value' => 100,
            'purchase_price' => 30,
        ]);

        $this->assertEquals(70, $item->gain_loss);
    }

    public function test_gain_loss_is_null_when_either_value_missing(): void
    {
        $item = Item::factory()->bare()->create([
            'estimated_value' => 100,
            'purchase_price' => null,
        ]);

        $this->assertNull($item->gain_loss);
    }

    public function test_status_and_condition_labels_resolve_from_constants(): void
    {
        $item = Item::factory()->bare()->create([
            'status' => 'loaned_out',
            'condition_rating' => 'like_new',
        ]);

        $this->assertSame('Loaned Out', $item->status_label);
        $this->assertSame('Like New', $item->condition_label);
    }

    public function test_soft_delete_and_restore_toggle_flags(): void
    {
        $item = Item::factory()->create();

        $item->softDelete();
        $this->assertTrue($item->fresh()->is_deleted);
        $this->assertNotNull($item->fresh()->deleted_at);

        $item->restore();
        $this->assertFalse($item->fresh()->is_deleted);
        $this->assertNull($item->fresh()->deleted_at);
    }

    public function test_active_and_trashed_scopes_partition_items(): void
    {
        $active = Item::factory()->create();
        $trashed = Item::factory()->trashed()->create();

        $this->assertEqualsCanonicalizing([$active->id], Item::active()->pluck('id')->all());
        $this->assertEqualsCanonicalizing([$trashed->id], Item::trashed()->pluck('id')->all());
    }

    public function test_in_collection_scope_excludes_sold_and_trashed(): void
    {
        $inCollection = Item::factory()->create(['status' => 'in_collection']);
        $sold = Item::factory()->create(['status' => 'sold']);
        $trashed = Item::factory()->trashed()->create(['status' => 'in_collection']);

        $ids = Item::inCollection()->pluck('id');
        $this->assertTrue($ids->contains($inCollection->id));
        $this->assertFalse($ids->contains($sold->id));
        $this->assertFalse($ids->contains($trashed->id));
    }

    public function test_favorites_scope_returns_only_favorited_active_items(): void
    {
        $favorite = Item::factory()->favorite()->create();
        Item::factory()->create();

        $this->assertEqualsCanonicalizing([$favorite->id], Item::favorites()->pluck('id')->all());
    }

    public function test_is_disposition_status(): void
    {
        $this->assertTrue(Item::factory()->bare()->make(['status' => 'sold'])->isDispositionStatus());
        $this->assertFalse(Item::factory()->bare()->make(['status' => 'in_collection'])->isDispositionStatus());
    }
}
