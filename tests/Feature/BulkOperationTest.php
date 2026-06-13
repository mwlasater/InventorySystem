<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\Tag;
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

    public function test_bulk_add_tags_creates_and_attaches_tags(): void
    {
        $user = User::factory()->create();
        $items = Item::factory()->count(2)->create();

        $this->actingAs($user)->post(route('items.bulk'), [
            'action' => 'add_tags',
            'tags' => ['Vintage', 'Rare'],
            'item_ids' => $items->pluck('id')->all(),
        ])->assertRedirect();

        foreach ($items as $item) {
            $this->assertEqualsCanonicalizing(
                ['Vintage', 'Rare'],
                $item->fresh()->tags->pluck('name')->all()
            );
        }
    }

    public function test_bulk_add_tags_does_not_duplicate_existing(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $item->tags()->attach(Tag::findOrCreateByName('Vintage'));

        $this->actingAs($user)->post(route('items.bulk'), [
            'action' => 'add_tags',
            'tags' => ['Vintage'],
            'item_ids' => [$item->id],
        ])->assertRedirect();

        $this->assertCount(1, $item->fresh()->tags);
    }

    public function test_bulk_remove_tags_detaches(): void
    {
        $user = User::factory()->create();
        $tag = Tag::findOrCreateByName('Fragile');
        $items = Item::factory()->count(2)->create();
        $items->each(fn ($i) => $i->tags()->attach($tag));

        $this->actingAs($user)->post(route('items.bulk'), [
            'action' => 'remove_tags',
            'tags' => ['Fragile'],
            'item_ids' => $items->pluck('id')->all(),
        ])->assertRedirect();

        foreach ($items as $item) {
            $this->assertCount(0, $item->fresh()->tags);
        }
    }

    public function test_bulk_restore_from_trash(): void
    {
        $user = User::factory()->create();
        $items = Item::factory()->count(2)->create(['is_deleted' => true, 'deleted_at' => now()]);

        $this->actingAs($user)->post(route('items.bulk.restore'), [
            'item_ids' => $items->pluck('id')->all(),
        ])->assertRedirect();

        foreach ($items as $item) {
            $this->assertFalse($item->fresh()->is_deleted);
        }
    }

    public function test_bulk_export_selection_returns_csv(): void
    {
        $user = User::factory()->create();
        $items = Item::factory()->count(2)->create();

        $response = $this->actingAs($user)->post(route('items.bulk.export'), [
            'item_ids' => $items->pluck('id')->all(),
        ]);

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Name,Description,Category', $csv);
        foreach ($items as $item) {
            $this->assertStringContainsString($item->name, $csv);
        }
    }

    public function test_bulk_export_requires_a_selection(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('items.bulk.export'), ['item_ids' => []])
            ->assertSessionHasErrors('item_ids');
    }

    public function test_trash_page_renders_for_admin_with_items(): void
    {
        // Regression: the view referenced route('trash.force-delete') while the
        // route is named 'trash.forceDelete', which 500s only for an admin
        // viewing a non-empty trash page.
        $admin = User::factory()->admin()->create();
        Item::factory()->create(['is_deleted' => true, 'deleted_at' => now()]);

        $this->actingAs($admin)->get(route('trash.index'))
            ->assertOk()
            ->assertSee('Delete Forever');
    }
}
