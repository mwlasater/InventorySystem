<?php

namespace Tests\Unit\Services;

use App\Models\Item;
use App\Services\DuplicateDetectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuplicateDetectionServiceTest extends TestCase
{
    use RefreshDatabase;

    private DuplicateDetectionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DuplicateDetectionService();
    }

    public function test_matches_on_barcode(): void
    {
        $item = Item::factory()->bare()->create(['barcode' => '0123456789', 'sku' => 'SKU-A']);

        $matches = $this->service->findPotentialDuplicates(['barcode' => '0123456789']);

        $this->assertTrue($matches->contains('id', $item->id));
    }

    public function test_matches_on_sku(): void
    {
        $item = Item::factory()->bare()->create(['sku' => 'SKU-UNIQUE']);

        $matches = $this->service->findPotentialDuplicates(['sku' => 'SKU-UNIQUE']);

        $this->assertTrue($matches->contains('id', $item->id));
    }

    public function test_matches_on_partial_name(): void
    {
        $item = Item::factory()->bare()->create(['name' => 'Vintage Red Chair']);

        $matches = $this->service->findPotentialDuplicates(['name' => 'Red Chair']);

        $this->assertTrue($matches->contains('id', $item->id));
    }

    public function test_excludes_given_id(): void
    {
        $item = Item::factory()->bare()->create(['barcode' => '999']);

        $matches = $this->service->findPotentialDuplicates(['barcode' => '999'], excludeId: $item->id);

        $this->assertTrue($matches->isEmpty());
    }

    public function test_ignores_trashed_items(): void
    {
        Item::factory()->bare()->trashed()->create(['barcode' => '555']);

        $matches = $this->service->findPotentialDuplicates(['barcode' => '555']);

        $this->assertTrue($matches->isEmpty());
    }

    public function test_results_are_unique_when_matched_on_multiple_fields(): void
    {
        $item = Item::factory()->bare()->create([
            'name' => 'Brass Lamp',
            'barcode' => 'B-1',
            'sku' => 'S-1',
        ]);

        $matches = $this->service->findPotentialDuplicates([
            'name' => 'Brass Lamp',
            'barcode' => 'B-1',
            'sku' => 'S-1',
        ]);

        $this->assertCount(1, $matches);
        $this->assertSame($item->id, $matches->first()->id);
    }
}
