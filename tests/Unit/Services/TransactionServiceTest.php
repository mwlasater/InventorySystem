<?php

namespace Tests\Unit\Services;

use App\Models\AuditLog;
use App\Models\Item;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionServiceTest extends TestCase
{
    use RefreshDatabase;

    private TransactionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TransactionService();
    }

    public function test_create_updates_item_status_and_records_audit(): void
    {
        $item = Item::factory()->status('in_collection')->create();

        $this->service->create($item, [
            'transaction_type' => 'sold',
            'transaction_date' => now()->subDay()->toDateString(),
            'sale_price' => 100,
            'shipping_cost' => 10,
        ]);

        $this->assertSame('sold', $item->fresh()->status);
        $this->assertDatabaseHas('audit_log', [
            'action' => 'transaction_created',
        ]);
    }

    public function test_create_calculates_net_proceeds(): void
    {
        $item = Item::factory()->create();

        $tx = $this->service->create($item, [
            'transaction_type' => 'sold',
            'transaction_date' => now()->subDay()->toDateString(),
            'sale_price' => 250,
            'shipping_cost' => 40,
        ]);

        $this->assertEquals(210, $tx->net_proceeds);
    }

    public function test_status_correction_is_always_allowed(): void
    {
        $item = Item::factory()->status('sold')->create();
        $this->assertNull($this->service->validateTransactionAllowed($item, 'status_correction'));
    }

    public function test_returned_only_allowed_when_loaned_out(): void
    {
        $loaned = Item::factory()->status('loaned_out')->create();
        $inCollection = Item::factory()->status('in_collection')->create();

        $this->assertNull($this->service->validateTransactionAllowed($loaned, 'returned'));
        $this->assertNotNull($this->service->validateTransactionAllowed($inCollection, 'returned'));
    }

    public function test_disposition_blocked_when_already_disposed(): void
    {
        $sold = Item::factory()->status('sold')->create();
        $this->assertNotNull($this->service->validateTransactionAllowed($sold, 'sold'));
    }

    public function test_disposition_allowed_from_collection_or_loaned_out(): void
    {
        $inCollection = Item::factory()->status('in_collection')->create();
        $loaned = Item::factory()->status('loaned_out')->create();

        $this->assertNull($this->service->validateTransactionAllowed($inCollection, 'sold'));
        $this->assertNull($this->service->validateTransactionAllowed($loaned, 'sold'));
    }
}
