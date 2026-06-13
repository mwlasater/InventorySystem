<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_loaning_an_item_sets_loaned_out_status(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->status('in_collection')->create();

        $this->actingAs($user)->post(route('items.transactions.store', $item), [
            'transaction_type' => 'loaned_out',
            'transaction_date' => now()->subDay()->toDateString(),
            'recipient_name' => 'Jane Borrower',
            'expected_return_date' => now()->addWeek()->toDateString(),
        ])->assertRedirect(route('items.show', $item));

        $this->assertSame('loaned_out', $item->fresh()->status);
        $this->assertDatabaseHas('transactions', [
            'item_id' => $item->id,
            'transaction_type' => 'loaned_out',
        ]);
    }

    public function test_returning_a_loaned_item_restores_collection_status(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->status('loaned_out')->create();

        $this->actingAs($user)->post(route('items.transactions.store', $item), [
            'transaction_type' => 'returned',
            'transaction_date' => now()->toDateString(),
        ])->assertRedirect();

        $this->assertSame('in_collection', $item->fresh()->status);
    }

    public function test_selling_calculates_net_proceeds(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->status('in_collection')->create();

        $this->actingAs($user)->post(route('items.transactions.store', $item), [
            'transaction_type' => 'sold',
            'transaction_date' => now()->toDateString(),
            'sale_price' => 200,
            'shipping_cost' => 25,
        ])->assertRedirect();

        $this->assertSame('sold', $item->fresh()->status);
        $this->assertEquals(175, Transaction::where('item_id', $item->id)->first()->net_proceeds);
    }

    public function test_cannot_dispose_an_already_disposed_item(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->status('sold')->create();

        $this->actingAs($user)->post(route('items.transactions.store', $item), [
            'transaction_type' => 'sold',
            'transaction_date' => now()->toDateString(),
        ])->assertSessionHasErrors('transaction_type');

        // Status is unchanged and no transaction was written.
        $this->assertSame('sold', $item->fresh()->status);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_future_transaction_date_is_rejected(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->status('in_collection')->create();

        $this->actingAs($user)->post(route('items.transactions.store', $item), [
            'transaction_type' => 'sold',
            'transaction_date' => now()->addWeek()->toDateString(),
        ])->assertSessionHasErrors('transaction_date');
    }
}
