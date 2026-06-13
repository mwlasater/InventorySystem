<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ItemWriteApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'New Widget',
            'category_id' => Category::factory()->create()->id,
            'location_id' => Location::factory()->create()->id,
            'quantity' => 1,
            'status' => 'in_collection',
            'estimated_value' => 250,
        ], $overrides);
    }

    public function test_create_item(): void
    {
        $response = $this->postJson('/api/v1/items', $this->validPayload(['tags' => ['Vintage']]));

        $response->assertCreated()->assertJsonPath('data.name', 'New Widget');
        $id = $response->json('data.id');

        $this->assertDatabaseHas('items', ['id' => $id, 'created_by' => $this->user->id]);
        // Valuation history fires automatically via the model trait.
        $this->assertDatabaseHas('item_valuations', ['item_id' => $id, 'value' => '250.00']);
        $this->assertSame(['Vintage'], Item::find($id)->tags->pluck('name')->all());
        $this->assertDatabaseHas('audit_log', ['action' => 'create', 'entity_id' => $id]);
    }

    public function test_create_validation_errors_return_422(): void
    {
        $this->postJson('/api/v1/items', ['name' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'category_id', 'location_id', 'quantity', 'status']);
    }

    public function test_update_item(): void
    {
        $item = Item::factory()->create(['status' => 'in_collection']);

        $this->putJson("/api/v1/items/{$item->id}", $this->validPayload(['name' => 'Renamed']))
            ->assertOk()
            ->assertJsonPath('data.name', 'Renamed');

        $this->assertSame('Renamed', $item->fresh()->name);
    }

    public function test_update_preserves_tags_when_omitted_but_replaces_when_present(): void
    {
        $item = Item::factory()->create(['status' => 'in_collection']);
        $item->tags()->attach(Tag::findOrCreateByName('Original'));

        // Omit tags -> unchanged
        $this->putJson("/api/v1/items/{$item->id}", $this->validPayload())->assertOk();
        $this->assertSame(['Original'], $item->fresh()->tags->pluck('name')->all());

        // Provide tags -> replaced
        $this->putJson("/api/v1/items/{$item->id}", $this->validPayload(['tags' => ['Replaced']]))->assertOk();
        $this->assertSame(['Replaced'], $item->fresh()->tags->pluck('name')->all());
    }

    public function test_update_404s_for_trashed_item(): void
    {
        $item = Item::factory()->create(['is_deleted' => true, 'deleted_at' => now()]);

        $this->putJson("/api/v1/items/{$item->id}", $this->validPayload())->assertNotFound();
    }

    public function test_delete_item_soft_deletes(): void
    {
        $item = Item::factory()->create();

        $this->deleteJson("/api/v1/items/{$item->id}")->assertNoContent();

        $this->assertTrue($item->fresh()->is_deleted);
        $this->assertDatabaseHas('audit_log', ['action' => 'delete', 'entity_id' => $item->id]);
    }

    public function test_record_transaction_updates_item_status(): void
    {
        $item = Item::factory()->create(['status' => 'in_collection']);

        $this->postJson("/api/v1/items/{$item->id}/transactions", [
            'transaction_type' => 'loaned_out',
            'transaction_date' => now()->subDay()->toDateString(),
            'recipient_name' => 'Jane',
            'expected_return_date' => now()->addWeek()->toDateString(),
        ])
            ->assertCreated()
            ->assertJsonPath('item.status', 'loaned_out')
            ->assertJsonPath('data.transaction_type', 'loaned_out');

        $this->assertSame('loaned_out', $item->fresh()->status);
    }

    public function test_invalid_transaction_for_state_returns_422(): void
    {
        $item = Item::factory()->create(['status' => 'in_collection']);
        // Sell it once (now in a disposition status)...
        $item->update(['status' => 'sold']);

        $this->postJson("/api/v1/items/{$item->id}/transactions", [
            'transaction_type' => 'sold',
            'transaction_date' => now()->subDay()->toDateString(),
        ])->assertStatus(422)->assertJsonValidationErrors('transaction_type');
    }
}
