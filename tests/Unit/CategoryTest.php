<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Quiz;
use App\Models\QuizLevel;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\QuizLevelSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Category model unit tests.
 * 
 * This test class verifies the core functionality of the Category model,
 * including creation, relationships with quizzes, and updates.
 */
class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     * 
     * Seeds the database with necessary data for testing category functionality.
     * 
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            CategorySeeder::class,
            RoleSeeder::class,
            QuizLevelSeeder::class,
        ]);
    }

    /**
     * Test the creation of a category.
     * 
     * Verifies that a category can be created with a name.
     *
     * @test
     * @return void
     */
    public function it_creates_a_category()
    {
        $category = new Category();
        $category->name = "Technology";
        $category->created_at = now();
        $category->save();

        $this->assertDatabaseHas("categories", [
            "id" => $category->id,
            "name" => "Technology",
        ]);

        $this->assertInstanceOf(Category::class, $category);
    }

    /**
     * Test retrieving seeded categories.
     * 
     * Verifies that the category seeder correctly populates the database.
     *
     * @test
     * @return void
     */
    public function it_retrieves_seeded_categories()
    {
        $categories = Category::all();
        
        $this->assertGreaterThanOrEqual(7, $categories->count());
        
        // Check for specific seeded categories
        $this->assertDatabaseHas('categories', [
            'name' => 'Sciences',
        ]);
        
        $this->assertDatabaseHas('categories', [
            'name' => 'Histoire',
        ]);
    }

    /**
     * Test updating a category's name.
     * 
     * Verifies that a category's name can be updated.
     *
     * @test
     * @return void
     */
    public function it_can_update_category_name()
    {
        $category = new Category();
        $category->name = "Original Category Name";
        $category->created_at = now();
        $category->save();
        
        $category->name = "Updated Category Name";
        $category->save();
        
        $this->assertDatabaseHas("categories", [
            "id" => $category->id,
            "name" => "Updated Category Name",
        ]);
        
        $refreshedCategory = $category->fresh();
        $this->assertEquals("Updated Category Name", $refreshedCategory->name);
    }

    /**
     * Test relationship between categories and quizzes.
     * 
     * Verifies that quizzes can be associated with a category and retrieved correctly.
     *
     * @test
     * @return void
     */
    public function it_can_have_multiple_quizzes()
    {
        $user = User::factory()->create();
        $category = new Category();
        $category->name = "Programming";
        $category->created_at = now();
        $category->save();
        $quizLevel = QuizLevel::first();
        
        // Create multiple quizzes for this category
        for ($i = 1; $i <= 3; $i++) {
            Quiz::create([
                "title" => "Programming Quiz {$i}",
                "slug" => "programming-quiz-{$i}",
                "description" => "Test quiz {$i} for programming category",
                "is_public" => true,
                "level_id" => $quizLevel->id,
                "status" => "published",
                "user_id" => $user->id,
                "duration" => 15,
                "pass_score" => 60,
                "category_id" => $category->id,
            ]);
        }
        
        $this->assertCount(3, $category->quizzes);
        
        // Verify each quiz belongs to this category
        foreach ($category->quizzes as $quiz) {
            $this->assertEquals($category->id, $quiz->category_id);
        }
    }

    /**
     * Test finding a category by name.
     * 
     * Verifies that categories can be found by their name.
     *
     * @test
     * @return void
     */
    public function it_can_find_category_by_name()
    {
        // Create a unique category for testing
        $categoryName = "Unique Test Category";
        $category = new Category();
        $category->name = $categoryName;
        $category->created_at = now();
        $category->save();
        
        $foundCategory = Category::where('name', $categoryName)->first();
        
        $this->assertNotNull($foundCategory);
        $this->assertEquals($categoryName, $foundCategory->name);
    }
    
    /**
     * Test querying for quizzes in a specific category.
     * 
     * Verifies that we can get all quizzes belonging to a specific category.
     *
     * @test
     * @return void
     */
    public function it_can_query_quizzes_by_category()
    {
        $user = User::factory()->create();
        $sportsCategory = Category::where('name', 'Sport')->first();
        $quizLevel = QuizLevel::first();
        
        if (!$sportsCategory) {
            $sportsCategory = new Category();
            $sportsCategory->name = 'Sport';
            $sportsCategory->created_at = now();
            $sportsCategory->save();
        }
        
        // Create quizzes for this category
        for ($i = 1; $i <= 3; $i++) {
            Quiz::create([
                "title" => "Sports Quiz {$i}",
                "slug" => "sports-quiz-{$i}",
                "description" => "Quiz about sports {$i}",
                "is_public" => true,
                "level_id" => $quizLevel->id,
                "status" => "published",
                "user_id" => $user->id,
                "duration" => 15,
                "pass_score" => 60,
                "category_id" => $sportsCategory->id,
            ]);
        }
        
        // Query quizzes by category
        $sportQuizzes = Quiz::where('category_id', $sportsCategory->id)->get();
        
        $this->assertCount(3, $sportQuizzes);
        $this->assertCount(3, $sportsCategory->quizzes);
        
        // Ensure they all belong to the Sports category
        foreach ($sportQuizzes as $quiz) {
            $this->assertEquals($sportsCategory->id, $quiz->category_id);
            $this->assertTrue(str_contains($quiz->title, 'Sports Quiz'));
        }
    }
}