<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'sub' => fake()->unique()->uuid(),
            'email' => fake()->unique()->safeEmail(),
            'name' => fake()->name(),
            'is_admin' => false,
        ];
    }

    public function admin(): static
    {
        return $this->state(['is_admin' => true]);
    }
}
