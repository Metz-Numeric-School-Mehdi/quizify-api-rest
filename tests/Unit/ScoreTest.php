<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Score;
use App\Models\User;
use App\Models\Quiz;
use App\Models\Category;
use App\Models\QuizLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RoleSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\QuizLevelSeeder;

/**
 * Score model unit tests.
 *
 * This test class verifies the core functionality of the Score model,
 * including creation, relationships with users and quizzes, and score calculations.
 */
class ScoreTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            RoleSeeder::class,
            CategorySeeder::class,
            QuizLevelSeeder::class,
        ]);
    }

    /**
     * Test creating a score.
     *
     * @test
     */
    public function it_creates_a_score()
    {
        $user = User::factory()->create();
        $category = Category::first();
        $level = QuizLevel::first();

        $quiz = Quiz::create([
            'title' => 'Test Quiz',
            'slug' => 'test-quiz',
            'description' => 'Quiz for score testing',
            'is_public' => true,
            'level_id' => $level->id,
            'status' => 'published',
            'user_id' => $user->id,
            'duration' => 30,
            'pass_score' => 70,
            'category_id' => $category->id,
        ]);

        $score = Score::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'score' => 85,
            'total_questions' => 10,
            'correct_answers' => 8,
            'time_taken' => 15,
        ]);

        $this->assertDatabaseHas('scores', [
            'id' => $score->id,
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'score' => 85,
            'total_questions' => 10,
            'correct_answers' => 8,
            'time_taken' => 15,
        ]);

        $this->assertInstanceOf(Score::class, $score);
    }

    /**
     * Test score belongs to user.
     *
     * @test
     */
    public function it_belongs_to_user()
    {
        $user = User::factory()->create();
        $category = Category::first();
        $level = QuizLevel::first();

        $quiz = Quiz::create([
            'title' => 'User Relationship Quiz',
            'slug' => 'user-relationship-quiz',
            'description' => 'Quiz to test user relationship',
            'is_public' => true,
            'level_id' => $level->id,
            'status' => 'published',
            'user_id' => $user->id,
            'duration' => 30,
            'pass_score' => 70,
            'category_id' => $category->id,
        ]);

        $score = Score::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'score' => 90,
            'total_questions' => 10,
            'correct_answers' => 9,
            'time_taken' => 20,
        ]);

        $this->assertInstanceOf(User::class, $score->user);
        $this->assertEquals($user->id, $score->user->id);
        $this->assertEquals($user->email, $score->user->email);
    }

    /**
     * Test score belongs to quiz.
     *
     * @test
     */
    public function it_belongs_to_quiz()
    {
        $user = User::factory()->create();
        $category = Category::first();
        $level = QuizLevel::first();

        $quiz = Quiz::create([
            'title' => 'Quiz Relationship Test',
            'slug' => 'quiz-relationship-test',
            'description' => 'Quiz to test quiz relationship',
            'is_public' => true,
            'level_id' => $level->id,
            'status' => 'published',
            'user_id' => $user->id,
            'duration' => 30,
            'pass_score' => 70,
            'category_id' => $category->id,
        ]);

        $score = Score::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'score' => 75,
            'total_questions' => 10,
            'correct_answers' => 7,
            'time_taken' => 25,
        ]);

        $this->assertInstanceOf(Quiz::class, $score->quiz);
        $this->assertEquals($quiz->id, $score->quiz->id);
        $this->assertEquals($quiz->title, $score->quiz->title);
    }

    /**
     * Test user can have multiple scores.
     *
     * @test
     */
    public function it_allows_multiple_scores_for_user()
    {
        $user = User::factory()->create();
        $category = Category::first();
        $level = QuizLevel::first();

        $quiz1 = Quiz::create([
            'title' => 'First Quiz',
            'slug' => 'first-quiz',
            'description' => 'First quiz for multiple scores',
            'is_public' => true,
            'level_id' => $level->id,
            'status' => 'published',
            'user_id' => $user->id,
            'duration' => 30,
            'pass_score' => 70,
            'category_id' => $category->id,
        ]);

        $quiz2 = Quiz::create([
            'title' => 'Second Quiz',
            'slug' => 'second-quiz',
            'description' => 'Second quiz for multiple scores',
            'is_public' => true,
            'level_id' => $level->id,
            'status' => 'published',
            'user_id' => $user->id,
            'duration' => 45,
            'pass_score' => 80,
            'category_id' => $category->id,
        ]);

        Score::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz1->id,
            'score' => 80,
            'total_questions' => 10,
            'correct_answers' => 8,
            'time_taken' => 20,
        ]);

        Score::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz2->id,
            'score' => 95,
            'total_questions' => 15,
            'correct_answers' => 14,
            'time_taken' => 30,
        ]);

        $userScores = $user->scores()->count();
        $this->assertEquals(2, $userScores);
    }

    /**
     * Test score validation constraints.
     *
     * @test
     */
    public function it_validates_score_constraints()
    {
        $user = User::factory()->create();
        $category = Category::first();
        $level = QuizLevel::first();

        $quiz = Quiz::create([
            'title' => 'Validation Quiz',
            'slug' => 'validation-quiz',
            'description' => 'Quiz for validation testing',
            'is_public' => true,
            'level_id' => $level->id,
            'status' => 'published',
            'user_id' => $user->id,
            'duration' => 30,
            'pass_score' => 70,
            'category_id' => $category->id,
        ]);

        // Test missing required fields
        $this->expectException(\Illuminate\Database\QueryException::class);

        Score::create([
            'quiz_id' => $quiz->id,
            'score' => 85,
            // Missing user_id
        ]);
    }

    /**
     * Test score percentage calculation.
     *
     * @test
     */
    public function it_calculates_percentage_correctly()
    {
        $user = User::factory()->create();
        $category = Category::first();
        $level = QuizLevel::first();

        $quiz = Quiz::create([
            'title' => 'Percentage Quiz',
            'slug' => 'percentage-quiz',
            'description' => 'Quiz for percentage calculation',
            'is_public' => true,
            'level_id' => $level->id,
            'status' => 'published',
            'user_id' => $user->id,
            'duration' => 30,
            'pass_score' => 70,
            'category_id' => $category->id,
        ]);

        $score = Score::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'score' => 80, // This should represent percentage
            'total_questions' => 10,
            'correct_answers' => 8,
            'time_taken' => 15,
        ]);

        // Test that score represents percentage correctly
        $this->assertEquals(80, $score->score);
        $this->assertEquals(8, $score->correct_answers);
        $this->assertEquals(10, $score->total_questions);

        // Calculate percentage manually and compare
        $expectedPercentage = ($score->correct_answers / $score->total_questions) * 100;
        $this->assertEquals(80, $expectedPercentage);
    }
}
