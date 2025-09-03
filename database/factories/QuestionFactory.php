<?php

namespace Database\Factories;

use App\Models\QuestionType;
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

    /**
     * Create an ordering question state.
     *
     * @return static
     */
    public function ordering(): static
    {
        return $this->state(function (array $attributes) {
            $orderingType = QuestionType::firstOrCreate(['name' => 'Remise dans l\'ordre']);

            return [
                'content' => $this->faker->randomElement([
                    'Remettez dans l\'ordre chronologique les étapes de développement',
                    'Ordonnez les couches du modèle OSI',
                    'Classez ces technologies par ordre d\'apparition',
                    'Mettez en ordre ces phases de projet'
                ]),
                'question_type_id' => $orderingType->id
            ];
        });
    }
}
