<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Models\Quiz;
use App\Models\Category;
use App\Models\QuizLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RoleSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\QuizLevelSeeder;

/**
 * QuizAttempt model unit tests.
 *
 * This test class verifies the core functionality of the QuizAttempt model,
 * including creation, relationships with users and quizzes, and attempt tracking.
 */
class QuizAttemptTest extends TestCase
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
     * Test creating a quiz attempt.
     *
     * @test
     */
    public function it_creates_a_quiz_attempt()
    {
        $user = User::factory()->create();
        $category = Category::first();
        $level = QuizLevel::first();

        $quiz = Quiz::create([
            'title' => 'Test Quiz',
            'slug' => 'test-quiz',
            'description' => 'Quiz for attempt testing',
            'is_public' => true,
            'level_id' => $level->id,
            'status' => 'published',
            'user_id' => $user->id,
            'duration' => 30,
            'pass_score' => 70,
            'category_id' => $category->id,
        ]);

        $attempt = QuizAttempt::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        $this->assertDatabaseHas('quiz_attempts', [
            'id' => $attempt->id,
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'status' => 'in_progress',
        ]);

        $this->assertInstanceOf(QuizAttempt::class, $attempt);
    }

    /**
     * Test quiz attempt belongs to user.
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

        $attempt = QuizAttempt::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'status' => 'completed',
            'started_at' => now()->subMinutes(30),
            'completed_at' => now(),
            'score' => 85,
        ]);

        $this->assertInstanceOf(User::class, $attempt->user);
        $this->assertEquals($user->id, $attempt->user->id);
        $this->assertEquals($user->email, $attempt->user->email);
    }

    /**
     * Test quiz attempt belongs to quiz.
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

        $attempt = QuizAttempt::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'status' => 'completed',
            'started_at' => now()->subMinutes(20),
            'completed_at' => now(),
            'score' => 90,
        ]);

        $this->assertInstanceOf(Quiz::class, $attempt->quiz);
        $this->assertEquals($quiz->id, $attempt->quiz->id);
        $this->assertEquals($quiz->title, $attempt->quiz->title);
    }

    /**
     * Test updating quiz attempt status.
     *
     * @test
     */
    public function it_can_update_attempt_status()
    {
        $user = User::factory()->create();
        $category = Category::first();
        $level = QuizLevel::first();

        $quiz = Quiz::create([
            'title' => 'Status Update Quiz',
            'slug' => 'status-update-quiz',
            'description' => 'Quiz for status update testing',
            'is_public' => true,
            'level_id' => $level->id,
            'status' => 'published',
            'user_id' => $user->id,
            'duration' => 30,
            'pass_score' => 70,
            'category_id' => $category->id,
        ]);

        $attempt = QuizAttempt::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        // Update to completed
        $attempt->update([
            'status' => 'completed',
            'completed_at' => now(),
            'score' => 75,
        ]);

        $this->assertDatabaseHas('quiz_attempts', [
            'id' => $attempt->id,
            'status' => 'completed',
            'score' => 75,
        ]);

        $this->assertNotNull($attempt->completed_at);
    }

    /**
     * Test user can have multiple attempts for different quizzes.
     *
     * @test
     */
    public function it_allows_multiple_attempts_for_different_quizzes()
    {
        $user = User::factory()->create();
        $category = Category::first();
        $level = QuizLevel::first();

        $quiz1 = Quiz::create([
            'title' => 'First Quiz',
            'slug' => 'first-quiz',
            'description' => 'First quiz for multiple attempts',
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
            'description' => 'Second quiz for multiple attempts',
            'is_public' => true,
            'level_id' => $level->id,
            'status' => 'published',
            'user_id' => $user->id,
            'duration' => 45,
            'pass_score' => 80,
            'category_id' => $category->id,
        ]);

        QuizAttempt::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz1->id,
            'status' => 'completed',
            'started_at' => now()->subHour(),
            'completed_at' => now()->subMinutes(30),
            'score' => 80,
        ]);

        QuizAttempt::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz2->id,
            'status' => 'completed',
            'started_at' => now()->subMinutes(45),
            'completed_at' => now(),
            'score' => 95,
        ]);

        $userAttempts = QuizAttempt::where('user_id', $user->id)->count();
        $this->assertEquals(2, $userAttempts);
    }

    /**
     * Test quiz attempt validation constraints.
     *
     * @test
     */
    public function it_validates_attempt_constraints()
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

        QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'status' => 'in_progress',
            // Missing user_id
        ]);
    }

    /**
     * Test calculating attempt duration.
     *
     * @test
     */
    public function it_can_calculate_attempt_duration()
    {
        $user = User::factory()->create();
        $category = Category::first();
        $level = QuizLevel::first();

        $quiz = Quiz::create([
            'title' => 'Duration Quiz',
            'slug' => 'duration-quiz',
            'description' => 'Quiz for duration calculation',
            'is_public' => true,
            'level_id' => $level->id,
            'status' => 'published',
            'user_id' => $user->id,
            'duration' => 30,
            'pass_score' => 70,
            'category_id' => $category->id,
        ]);

        $startTime = now()->subMinutes(25);
        $endTime = now();

        $attempt = QuizAttempt::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'status' => 'completed',
            'started_at' => $startTime,
            'completed_at' => $endTime,
            'score' => 88,
        ]);

        $this->assertNotNull($attempt->started_at);
        $this->assertNotNull($attempt->completed_at);

        // Verify the timestamps are correctly stored - completed should be after started
        $this->assertGreaterThanOrEqual($attempt->started_at, $attempt->completed_at);

        // Verify duration is reasonable (25 minutes in this test case)
        $durationInMinutes = $attempt->started_at->diffInMinutes($attempt->completed_at);
        $this->assertEquals(25, $durationInMinutes);
    }
}
