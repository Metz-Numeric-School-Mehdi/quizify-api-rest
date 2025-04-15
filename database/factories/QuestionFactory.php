<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "content" => $this->faker->sentence,
            "quiz_id" => $this->faker->numberBetween(1, 10),
            "question_type_id" => $this->faker->numberBetween(1, 3),
        ];
    }
}
