<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The shared hash of the default password, computed once.
     */
    protected static ?string $password = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'display_name' => fake()->name(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'user',
            'permissions' => [],
            'is_active' => true,
            'force_password_change' => false,
            'failed_login_attempts' => 0,
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'admin']);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }

    public function mustChangePassword(): static
    {
        return $this->state(fn (array $attributes) => ['force_password_change' => true]);
    }

    /**
     * Set a known plaintext password (so tests can log in with it).
     */
    public function password(string $plain): static
    {
        return $this->state(fn (array $attributes) => ['password' => Hash::make($plain)]);
    }
}
