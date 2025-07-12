<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Quiz;
use App\Models\User;
use App\Models\Category;
use App\Models\QuizLevel;
use App\Models\Role;
use Database\Seeders\CategorySeeder;
use Database\Seeders\QuizLevel as SeedersQuizLevel;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuizTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_quiz_with_dependencies()
    {
        $this->seed(RoleSeeder::class);
        $role = Role::where("name", "admin")->first();
        $user = User::factory()->create([
            "role_id" => $role->id,
        ]);
        $this->seed(CategorySeeder::class);
        $category = Category::first();
        $this->seed(SeedersQuizLevel::class);
        $level = QuizLevel::first();

        $quiz = Quiz::create([
            "title" => "Quiz Laravel",
            "slug" => "quiz-laravel",
            "description" => "Testez vos connaissances sur Laravel.",
            "is_public" => true,
            "level_id" => 1,
            "status" => "published",
            "user_id" => 1,
            "duration" => 30,
            "pass_score" => 70,
            "thumbnail" => null,
            "category_id" => 1,
        ]);

        $this->assertDatabaseHas("quizzes", [
            "id" => $quiz->id,
            "user_id" => $user->id,
            "category_id" => $category->id,
            "level_id" => $level->id,
        ]);

        $this->assertInstanceOf(User::class, $quiz->user);
        $this->assertInstanceOf(Category::class, $quiz->category);
        $this->assertInstanceOf(QuizLevel::class, $quiz->level);
    }
}
