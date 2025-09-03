<?php

namespace Database\Factories;

use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Answer>
 */
class AnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "question_id" => $this->faker->numberBetween(1, 10),
            "content" => $this->faker->sentence,
            "is_correct" => $this->faker->boolean,
        ];
    }

    /**
     * Create an ordered answer state.
     *
     * @param int $position
     * @return static
     */
    public function ordered(int $position): static
    {
        return $this->state(function (array $attributes) use ($position) {
            return [
                'is_correct' => true,
                'order_position' => $position,
                'content' => $this->faker->randomElement([
                    'Étape ' . $position,
                    'Phase ' . $position,
                    'Niveau ' . $position,
                    'Processus ' . $position
                ])
            ];
        });
    }
}
