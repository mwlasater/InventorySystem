<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_streams_a_csv_of_active_items(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['name' => 'Exportable Gadget']);
        Item::factory()->trashed()->create(['name' => 'Should Not Appear']);

        $response = $this->actingAs($user)->get(route('export.items'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        $body = $response->streamedContent();
        $this->assertStringContainsString('Exportable Gadget', $body);
        $this->assertStringNotContainsString('Should Not Appear', $body);
        // Header row is present.
        $this->assertStringContainsString('Name', $body);
    }

    public function test_export_requires_authentication(): void
    {
        $this->get(route('export.items'))->assertRedirect(route('login'));
    }
}
