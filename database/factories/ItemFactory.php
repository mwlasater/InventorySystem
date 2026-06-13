<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'name' => ucfirst(fake()->words(3, true)),
            'description' => fake()->optional()->sentence(),
            'category_id' => Category::factory(),
            'location_id' => Location::factory(),
            'condition_rating' => fake()->randomElement(array_keys(Item::CONDITION_LABELS)),
            'quantity' => 1,
            'acquisition_date' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'acquisition_method' => fake()->randomElement(array_keys(Item::ACQUISITION_METHODS)),
            'purchase_price' => fake()->randomFloat(2, 5, 500),
            'purchase_currency' => 'USD',
            'estimated_value' => fake()->randomFloat(2, 5, 1000),
            'status' => 'in_collection',
            'is_favorite' => false,
            'is_deleted' => false,
        ];
    }

    public function trashed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_deleted' => true,
            'deleted_at' => now(),
        ]);
    }

    public function favorite(): static
    {
        return $this->state(fn (array $attributes) => ['is_favorite' => true]);
    }

    public function status(string $status): static
    {
        return $this->state(fn (array $attributes) => ['status' => $status]);
    }

    /**
     * No category/location — useful for filter/duplicate tests that don't need them.
     */
    public function bare(): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => null,
            'location_id' => null,
        ]);
    }
}
