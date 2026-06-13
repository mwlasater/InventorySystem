<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
 */
class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->streetName(),
            'parent_id' => null,
            'level' => 'shelf',
            'description' => null,
            'sort_order' => 0,
        ];
    }

    public function level(string $level): static
    {
        return $this->state(fn (array $attributes) => ['level' => $level]);
    }
}
