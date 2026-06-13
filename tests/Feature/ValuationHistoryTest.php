<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValuationHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_item_with_a_value_records_an_initial_valuation(): void
    {
        $item = Item::factory()->create([
            'estimated_value' => 100,
            'purchase_currency' => 'USD',
            'valuation_source' => 'appraisal',
        ]);

        $this->assertCount(1, $item->valuations);
        $valuation = $item->valuations->first();
        $this->assertSame('100.00', $valuation->value);
        $this->assertSame('USD', $valuation->currency);
        $this->assertSame('appraisal', $valuation->source);
    }

    public function test_creating_item_without_a_value_records_nothing(): void
    {
        $item = Item::factory()->create(['estimated_value' => null]);

        $this->assertCount(0, $item->valuations);
    }

    public function test_changing_estimated_value_appends_a_valuation(): void
    {
        $item = Item::factory()->create(['estimated_value' => 100]);

        $item->update(['estimated_value' => 150]);

        $this->assertCount(2, $item->fresh()->valuations);
        $this->assertSame('150.00', $item->fresh()->valuations->first()->value);
    }

    public function test_non_value_changes_do_not_append_a_valuation(): void
    {
        $item = Item::factory()->create(['estimated_value' => 100]);

        $item->update(['name' => 'Renamed', 'notes' => 'edited']);

        $this->assertCount(1, $item->fresh()->valuations);
    }

    public function test_clearing_the_value_does_not_append_a_valuation(): void
    {
        $item = Item::factory()->create(['estimated_value' => 100]);

        $item->update(['estimated_value' => null]);

        $this->assertCount(1, $item->fresh()->valuations);
    }

    public function test_valuation_uses_item_valuation_date_when_present(): void
    {
        $item = Item::factory()->create([
            'estimated_value' => 100,
            'valuation_date' => '2025-01-15',
        ]);

        $this->assertSame('2025-01-15', $item->valuations->first()->valued_at->toDateString());
    }

    public function test_deleting_an_item_cascades_to_its_valuations(): void
    {
        $item = Item::factory()->create(['estimated_value' => 100]);
        $this->assertDatabaseHas('item_valuations', ['item_id' => $item->id]);

        $item->delete();

        $this->assertDatabaseMissing('item_valuations', ['item_id' => $item->id]);
    }

    public function test_item_page_shows_valuation_history(): void
    {
        $item = Item::factory()->create(['estimated_value' => 100]);
        $item->update(['estimated_value' => 175]);

        $this->actingAs(User::factory()->create())
            ->get(route('items.show', $item))
            ->assertOk()
            ->assertSee('Valuation History')
            ->assertSee('$175.00')
            ->assertSee('$100.00');
    }
}
