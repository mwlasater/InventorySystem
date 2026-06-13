<?php

namespace Tests\Unit\Services;

use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use App\Services\ImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportServiceTest extends TestCase
{
    use RefreshDatabase;

    private ImportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ImportService;
    }

    private function makeCsv(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'import_').'.csv';
        file_put_contents($path, $contents);

        return $path;
    }

    public function test_suggest_mappings_matches_known_headers(): void
    {
        $mappings = $this->service->suggestMappings(['Name', 'Barcode', 'Quantity', 'Estimated Value']);

        $this->assertSame('name', $mappings['Name']);
        $this->assertSame('barcode', $mappings['Barcode']);
        $this->assertSame('quantity', $mappings['Quantity']);
        $this->assertSame('estimated_value', $mappings['Estimated Value']);
    }

    public function test_validate_flags_missing_name_and_invalid_status(): void
    {
        $csv = $this->makeCsv(
            "Name,Status\n".
            "Good Item,in_collection\n".
            ",in_collection\n".          // missing name
            "Bad Status Item,banana\n"     // invalid status
        );

        $mappings = ['Name' => 'name', 'Status' => 'status'];
        $result = $this->service->validate($csv, $mappings);

        $this->assertSame(3, $result['total_count']);
        $this->assertSame(1, $result['valid_count']);
        $this->assertSame(2, $result['error_count']);

        $fields = array_column($result['errors'], 'field');
        $this->assertContains('name', $fields);
        $this->assertContains('status', $fields);
    }

    public function test_validate_requires_existing_category(): void
    {
        Category::create(['name' => 'Tools']);

        $csv = $this->makeCsv(
            "Name,Category\n".
            "Hammer,Tools\n".
            "Wrench,Nonexistent\n"
        );

        $result = $this->service->validate($csv, ['Name' => 'name', 'Category' => 'category']);

        $this->assertSame(1, $result['valid_count']);
        $this->assertSame(1, $result['error_count']);
        $this->assertSame('category', $result['errors'][0]['field']);
    }

    public function test_execute_imports_rows_and_creates_related_records(): void
    {
        $csv = $this->makeCsv(
            "Name,Category,Location,Tags,Estimated Value\n".
            "Drill,Power Tools,Garage Shelf,\"electric,heavy\",75.50\n".
            ",Power Tools,Garage Shelf,,10\n"   // missing name -> skipped
        );

        $mappings = [
            'Name' => 'name',
            'Category' => 'category',
            'Location' => 'location',
            'Tags' => 'tags',
            'Estimated Value' => 'estimated_value',
        ];

        $user = User::factory()->create();
        $result = $this->service->execute($csv, $mappings, userId: $user->id);

        $this->assertSame(1, $result['imported']);
        $this->assertSame(1, $result['skipped']);

        $this->assertDatabaseHas('items', ['name' => 'Drill', 'estimated_value' => 75.50]);
        $this->assertDatabaseHas('categories', ['name' => 'Power Tools']);
        $this->assertDatabaseHas('locations', ['name' => 'Garage Shelf']);

        $item = Item::where('name', 'Drill')->first();
        $this->assertEqualsCanonicalizing(['electric', 'heavy'], $item->tags->pluck('name')->all());
    }
}
