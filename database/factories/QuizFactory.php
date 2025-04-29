<?php

namespace Database\Factories;

use App\Models\Tag;
use App\Models\Quiz;
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
            "title" => fake()->sentence(),
            "slug" => fake()->slug(),
            "description" => fake()->paragraph(),
            "is_public" => fake()->boolean(),
            "status" => fake()->randomElement(["draft", "published", "archived"]),
            "level_id" => fake()->numberBetween(1, 3),
            "user_id" => fake()->numberBetween(1, 20),
            "duration" => fake()->numberBetween(10, 120),
            "max_attempts" => fake()->numberBetween(1, 5),
            "pass_score" => fake()->numberBetween(50, 100),
            "category_id" => fake()->numberBetween(1, 7),
            "thumbnail" => fake()->imageUrl(),
        ];
    }

    /**
     * Configure the factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (Quiz $quiz) {
            $tagIds = Tag::inRandomOrder()->limit(rand(1, 3))->pluck("id")->toArray();
            $quiz->tags()->sync($tagIds);
        });
    }
}
