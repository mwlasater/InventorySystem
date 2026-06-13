<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\ItemValuation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ItemValuation>
 */
class ItemValuationFactory extends Factory
{
    protected $model = ItemValuation::class;

    public function definition(): array
    {
        return [
            'item_id' => Item::factory(),
            'value' => fake()->randomFloat(2, 5, 1000),
            'currency' => 'USD',
            'source' => fake()->randomElement(['appraisal', 'market estimate', 'eBay comps', null]),
            'valued_at' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
        ];
    }
}
