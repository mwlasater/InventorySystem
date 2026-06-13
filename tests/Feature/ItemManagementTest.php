<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemManagementTest extends TestCase
{
    use RefreshDatabase;

    private function actingUser(): User
    {
        return User::factory()->create();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('items.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_item_list(): void
    {
        Item::factory()->count(3)->create();

        $this->actingAs($this->actingUser())
            ->get(route('items.index'))
            ->assertOk();
    }

    public function test_item_can_be_created(): void
    {
        $user = $this->actingUser();
        $category = Category::factory()->create();
        $location = Location::factory()->create();

        $response = $this->actingAs($user)->post(route('items.store'), [
            'name' => 'Test Widget',
            'category_id' => $category->id,
            'location_id' => $location->id,
            'quantity' => 1,
            'status' => 'in_collection',
            'skip_duplicate_check' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('items', [
            'name' => 'Test Widget',
            'created_by' => $user->id,
            'is_deleted' => false,
        ]);
    }

    public function test_item_creation_validates_required_fields(): void
    {
        $this->actingAs($this->actingUser())
            ->post(route('items.store'), ['skip_duplicate_check' => 1])
            ->assertSessionHasErrors(['name', 'category_id', 'location_id', 'quantity']);
    }

    public function test_item_cannot_be_created_with_a_disposition_status(): void
    {
        $category = Category::factory()->create();
        $location = Location::factory()->create();

        $this->actingAs($this->actingUser())
            ->post(route('items.store'), [
                'name' => 'Bad Status',
                'category_id' => $category->id,
                'location_id' => $location->id,
                'quantity' => 1,
                'status' => 'sold',
                'skip_duplicate_check' => 1,
            ])
            ->assertSessionHasErrors('status');
    }

    public function test_item_can_be_updated(): void
    {
        $user = $this->actingUser();
        $item = Item::factory()->create(['name' => 'Old Name']);

        $this->actingAs($user)->put(route('items.update', $item), [
            'name' => 'New Name',
            'category_id' => $item->category_id,
            'location_id' => $item->location_id,
            'quantity' => 2,
            'status' => 'in_collection',
        ])->assertRedirect();

        $this->assertDatabaseHas('items', ['id' => $item->id, 'name' => 'New Name', 'quantity' => 2]);
    }

    public function test_update_rejects_disposition_status_change(): void
    {
        $user = $this->actingUser();
        $item = Item::factory()->create();

        $this->actingAs($user)->put(route('items.update', $item), [
            'name' => $item->name,
            'category_id' => $item->category_id,
            'location_id' => $item->location_id,
            'quantity' => 1,
            'status' => 'sold',
        ])->assertSessionHasErrors('status');
    }

    public function test_destroy_soft_deletes_the_item(): void
    {
        $user = $this->actingUser();
        $item = Item::factory()->create();

        $this->actingAs($user)->delete(route('items.destroy', $item))
            ->assertRedirect(route('items.index'));

        $this->assertDatabaseHas('items', ['id' => $item->id, 'is_deleted' => true]);
        $this->assertDatabaseHas('audit_log', ['action' => 'delete', 'entity_id' => $item->id]);
    }

    public function test_toggle_favorite(): void
    {
        $user = $this->actingUser();
        $item = Item::factory()->create(['is_favorite' => false]);

        $this->actingAs($user)->post(route('items.favorite', $item));
        $this->assertTrue($item->fresh()->is_favorite);

        $this->actingAs($user)->post(route('items.favorite', $item));
        $this->assertFalse($item->fresh()->is_favorite);
    }
}
