<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrashTest extends TestCase
{
    use RefreshDatabase;

    public function test_trashed_item_can_be_restored(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->trashed()->create();

        $this->actingAs($user)->post(route('trash.restore', $item))->assertRedirect();

        $this->assertDatabaseHas('items', ['id' => $item->id, 'is_deleted' => false]);
        $this->assertDatabaseHas('audit_log', ['action' => 'restore', 'entity_id' => $item->id]);
    }

    public function test_trashed_item_can_be_permanently_deleted(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->trashed()->create();

        $this->actingAs($user)->delete(route('trash.forceDelete', $item))->assertRedirect();

        $this->assertDatabaseMissing('items', ['id' => $item->id]);
    }
}
