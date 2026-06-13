<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'item_id' => Item::factory(),
            'transaction_type' => 'sold',
            'transaction_date' => now()->subDay()->toDateString(),
            'recipient_name' => fake()->name(),
            'sale_price' => fake()->randomFloat(2, 10, 500),
            'shipping_cost' => fake()->randomFloat(2, 0, 30),
        ];
    }

    public function type(string $type): static
    {
        return $this->state(fn (array $attributes) => ['transaction_type' => $type]);
    }

    public function loanedOut(?string $expectedReturn = null): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_type' => 'loaned_out',
            'expected_return_date' => $expectedReturn,
        ]);
    }
}
