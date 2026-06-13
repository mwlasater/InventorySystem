<?php

namespace Tests\Unit\Models;

use App\Models\Item;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_resulting_status_maps_each_type(): void
    {
        $cases = [
            'sold' => 'sold',
            'given_away' => 'given_away',
            'traded' => 'traded',
            'loaned_out' => 'loaned_out',
            'returned' => 'in_collection',
            'lost' => 'lost',
            'disposed' => 'disposed',
            'status_correction' => 'in_collection',
        ];

        foreach ($cases as $type => $expected) {
            $tx = Transaction::factory()->make(['transaction_type' => $type]);
            $this->assertSame($expected, $tx->getResultingStatus(), "type {$type}");
        }
    }

    public function test_disposition_and_restoration_classification(): void
    {
        $this->assertTrue(Transaction::factory()->make(['transaction_type' => 'sold'])->isDisposition());
        $this->assertFalse(Transaction::factory()->make(['transaction_type' => 'returned'])->isDisposition());

        $this->assertTrue(Transaction::factory()->make(['transaction_type' => 'returned'])->isRestoration());
        $this->assertTrue(Transaction::factory()->make(['transaction_type' => 'status_correction'])->isRestoration());
        $this->assertFalse(Transaction::factory()->make(['transaction_type' => 'sold'])->isRestoration());
    }

    public function test_type_label(): void
    {
        $this->assertSame('Given Away', Transaction::factory()->make(['transaction_type' => 'given_away'])->type_label);
    }

    public function test_loan_is_overdue_when_past_due_and_not_returned(): void
    {
        $item = Item::factory()->status('loaned_out')->create();
        $loan = Transaction::factory()->for($item)->loanedOut(now()->subDays(2)->toDateString())
            ->create(['created_at' => now()->subMinutes(10)]);

        $this->assertTrue($loan->isLoanOverdue());
    }

    public function test_loan_is_not_overdue_when_due_in_future(): void
    {
        $item = Item::factory()->status('loaned_out')->create();
        $loan = Transaction::factory()->for($item)->loanedOut(now()->addDays(5)->toDateString())->create();

        $this->assertFalse($loan->isLoanOverdue());
    }

    public function test_loan_is_not_overdue_after_a_later_return(): void
    {
        $item = Item::factory()->status('loaned_out')->create();
        $loan = Transaction::factory()->for($item)->loanedOut(now()->subDays(2)->toDateString())
            ->create(['created_at' => now()->subMinutes(10)]);

        Transaction::factory()->for($item)->type('returned')->create(['created_at' => now()]);

        $this->assertFalse($loan->fresh()->isLoanOverdue());
    }

    public function test_non_loan_transaction_is_never_overdue(): void
    {
        $tx = Transaction::factory()->type('sold')->create();
        $this->assertFalse($tx->isLoanOverdue());
    }
}
