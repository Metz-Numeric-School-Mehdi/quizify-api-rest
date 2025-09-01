<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\QuizLevel;
use App\Models\Quiz;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\QuizLevelSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RoleSeeder;

/**
 * QuizLevel model unit tests.
 *
 * This test class verifies the core functionality of the QuizLevel model,
 * including creation, relationships with quizzes, and CRUD operations.
 */
class QuizLevelTest extends TestCase
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
            QuizLevelSeeder::class,
            CategorySeeder::class,
        ]);
    }

    /**
     * Test creating a quiz level.
     *
     * @test
     */
    public function it_creates_a_quiz_level()
    {
        $level = QuizLevel::create([
            'name' => 'Expert',
            'description' => 'Expert level quizzes for advanced users',
        ]);

        $this->assertDatabaseHas('quiz_levels', [
            'id' => $level->id,
            'name' => 'Expert',
            'description' => 'Expert level quizzes for advanced users',
        ]);

        $this->assertInstanceOf(QuizLevel::class, $level);
    }

    /**
     * Test retrieving seeded quiz levels.
     *
     * @test
     */
    public function it_retrieves_seeded_quiz_levels()
    {
        // QuizLevelSeeder should create some default levels
        $levels = QuizLevel::all();

        $this->assertGreaterThan(0, $levels->count());

        // Check for common difficulty levels from seeder
        $easyLevel = QuizLevel::where('name', 'Facile')->first();
        $this->assertNotNull($easyLevel);
    }

    /**
     * Test quiz level can have multiple quizzes.
     *
     * @test
     */
    public function it_can_have_multiple_quizzes()
    {
        $level = QuizLevel::first();
        $category = Category::first();
        $user = User::factory()->create();

        $quiz1 = Quiz::create([
            'title' => 'Quiz 1',
            'slug' => 'quiz-1',
            'description' => 'First quiz with this level',
            'is_public' => true,
            'level_id' => $level->id,
            'status' => 'published',
            'user_id' => $user->id,
            'duration' => 30,
            'pass_score' => 70,
            'category_id' => $category->id,
        ]);

        $quiz2 = Quiz::create([
            'title' => 'Quiz 2',
            'slug' => 'quiz-2',
            'description' => 'Second quiz with this level',
            'is_public' => true,
            'level_id' => $level->id,
            'status' => 'published',
            'user_id' => $user->id,
            'duration' => 45,
            'pass_score' => 80,
            'category_id' => $category->id,
        ]);

        // Verify quizzes have the level
        $this->assertEquals($level->id, $quiz1->level_id);
        $this->assertEquals($level->id, $quiz2->level_id);

        // Verify level has quizzes
        $quizzesCount = $level->quizzes()->count();
        $this->assertEquals(2, $quizzesCount);
    }

    /**
     * Test updating quiz level attributes.
     *
     * @test
     */
    public function it_can_update_quiz_level_attributes()
    {
        $level = QuizLevel::create([
            'name' => 'Original Level',
            'description' => 'Original level description',
        ]);

        $level->update([
            'name' => 'Updated Level',
            'description' => 'Updated level description',
        ]);

        $this->assertDatabaseHas('quiz_levels', [
            'id' => $level->id,
            'name' => 'Updated Level',
            'description' => 'Updated level description',
        ]);
    }

    /**
     * Test quiz level name is required.
     *
     * @test
     */
    public function it_requires_name_field()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        QuizLevel::create([
            'description' => 'Level without name',
        ]);
    }

    /**
     * Test finding quiz level by name.
     *
     * @test
     */
    public function it_can_find_quiz_level_by_name()
    {
        $level = QuizLevel::create([
            'name' => 'Unique Level Name',
            'description' => 'Searchable level',
        ]);

        $found = QuizLevel::where('name', 'Unique Level Name')->first();

        $this->assertNotNull($found);
        $this->assertEquals($level->id, $found->id);
        $this->assertEquals('Unique Level Name', $found->name);
    }

    /**
     * Test quiz level relationships work correctly.
     *
     * @test
     */
    public function it_has_correct_relationship_with_quiz()
    {
        $level = QuizLevel::first();
        $category = Category::first();
        $user = User::factory()->create();

        $quiz = Quiz::create([
            'title' => 'Relationship Test Quiz',
            'slug' => 'relationship-test-quiz',
            'description' => 'Quiz to test level relationship',
            'is_public' => true,
            'level_id' => $level->id,
            'status' => 'published',
            'user_id' => $user->id,
            'duration' => 30,
            'pass_score' => 70,
            'category_id' => $category->id,
        ]);

        // Test level -> quiz relationship
        $this->assertTrue($level->quizzes->contains($quiz));

        // Test quiz -> level relationship
        $this->assertInstanceOf(QuizLevel::class, $quiz->level);
        $this->assertEquals($level->id, $quiz->level->id);
    }
}
