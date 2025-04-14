<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quiz>
 */
class QuizFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "title" => fake()->title(),
            "description" => fake()->paragraph(),
            "is_public" => fake()->boolean(),
            "user_id" => fake()->numberBetween(1, 20),
            "level_id" => fake()->numberBetween(1, 3),
        ];
    }
}
