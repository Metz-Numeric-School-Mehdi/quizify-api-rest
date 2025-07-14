<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Quiz;
use App\Models\User;
use App\Models\Category;
use App\Models\QuizLevel;
use App\Models\Role;
use App\Models\Question;
use App\Models\Tag;
use Database\Seeders\CategorySeeder;
use Database\Seeders\QuestionTypeSeeder;
use Database\Seeders\QuizLevelSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\TagSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Quiz model unit tests.
 * 
 * This test class verifies the core functionality of the Quiz model,
 * including creation, relationships, updates, and soft deletion.
 */
class QuizTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     * 
     * Seeds the database with necessary data for testing quiz functionality,
     * including roles, categories, and quiz levels.
     * 
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            RoleSeeder::class,
            CategorySeeder::class,
            QuizLevelSeeder::class,
            QuestionTypeSeeder::class,
        ]);
    }

    /**
     * Test creating a quiz with its dependencies.
     * 
     * Verifies that a quiz can be created with proper relationships
     * to user, category, and quiz level.
     *
     * @test
     * @return void
     */
    public function it_creates_a_quiz_with_dependencies()
    {
        $role = Role::where("name", "admin")->first();
        $user = User::factory()->create([
            "role_id" => $role->id,
        ]);
        $category = Category::first();
        $level = QuizLevel::first();

        $quiz = Quiz::create([
            "title" => "Quiz Laravel",
            "slug" => "quiz-laravel",
            "description" => "Testez vos connaissances sur Laravel.",
            "is_public" => true,
            "level_id" => $level->id,
            "status" => "published",
            "user_id" => $user->id,
            "duration" => 30,
            "pass_score" => 70,
            "thumbnail" => null,
            "category_id" => $category->id,
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
    
    /**
     * Test creating a quiz using factory.
     * 
     * Ensures the Quiz factory works correctly and creates valid records.
     *
     * @test
     * @return void
     */
    public function it_creates_a_quiz_using_factory()
    {
        $user = User::factory()->create();
        $category = Category::first();
        $level = QuizLevel::first();
        
        $quiz = Quiz::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'level_id' => $level->id,
        ]);
        
        $this->assertDatabaseHas('quizzes', [
            'id' => $quiz->id,
            'title' => $quiz->title,
        ]);
        
        $this->assertEquals($user->id, $quiz->user_id);
        $this->assertEquals($category->id, $quiz->category_id);
        $this->assertEquals($level->id, $quiz->level_id);
    }
    
    /**
     * Test adding questions to a quiz.
     * 
     * Verifies that questions can be added to a quiz and
     * the relationship is properly established.
     *
     * @test
     * @return void
     */
    public function it_can_add_questions_to_quiz()
    {
        $user = User::factory()->create();
        $quiz = Quiz::factory()->create([
            'user_id' => $user->id,
            'category_id' => Category::first()->id,
            'level_id' => QuizLevel::first()->id,
        ]);
        
        // Create 3 questions for the quiz
        $questionType = \App\Models\QuestionType::first();
        
        for ($i = 0; $i < 3; $i++) {
            Question::create([
                'quiz_id' => $quiz->id,
                'content' => "Test question {$i}",
                'question_type_id' => $questionType->id,
            ]);
        }
        
        $this->assertCount(3, $quiz->questions);
        $this->assertInstanceOf(Question::class, $quiz->questions->first());
    }
    
    /**
     * Test soft deleting a quiz.
     * 
     * Verifies that when a quiz is deleted, it is soft deleted and
     * can still be found in the database with a deletion timestamp.
     *
     * @test
     * @return void
     */
    public function it_can_soft_delete_a_quiz()
    {
        $user = User::factory()->create();
        $quiz = Quiz::factory()->create([
            'user_id' => $user->id,
            'category_id' => Category::first()->id,
            'level_id' => QuizLevel::first()->id,
        ]);
        
        $quizId = $quiz->id;
        $quiz->delete();
        
        $this->assertSoftDeleted('quizzes', ['id' => $quizId]);
    }
    
    /**
     * Test updating quiz attributes.
     * 
     * Checks that a quiz can be updated and changes are saved to the database.
     *
     * @test
     * @return void
     */
    public function it_can_update_quiz_attributes()
    {
        $user = User::factory()->create();
        $quiz = Quiz::factory()->create([
            'user_id' => $user->id,
            'category_id' => Category::first()->id,
            'level_id' => QuizLevel::first()->id,
            'title' => 'Original Title',
            'description' => 'Original Description',
            'is_public' => true,
        ]);
        
        $quiz->update([
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'is_public' => false,
        ]);
        
        $this->assertDatabaseHas('quizzes', [
            'id' => $quiz->id,
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'is_public' => 0,
        ]);
        
        $refreshedQuiz = $quiz->fresh();
        $this->assertEquals('Updated Title', $refreshedQuiz->title);
        $this->assertEquals('Updated Description', $refreshedQuiz->description);
        $this->assertFalse((bool)$refreshedQuiz->is_public);
    }
    
    /**
     * Test quiz relationships with tags.
     * 
     * Verifies that tags can be attached to a quiz and the many-to-many
     * relationship functions correctly.
     *
     * @test
     * @return void
     */
    public function it_can_associate_tags_with_quiz()
    {
        $this->seed(TagSeeder::class);
        
        $user = User::factory()->create();
        $quiz = Quiz::factory()->create([
            'user_id' => $user->id,
            'category_id' => Category::first()->id,
            'level_id' => QuizLevel::first()->id,
        ]);
        
        // Assurons-nous de récupérer exactement 3 tags spécifiques
        $tags = Tag::limit(3)->get();
        $tagIds = $tags->pluck('id')->toArray();
        
        // Détachons d'abord tous les tags existants pour être sûr
        $quiz->tags()->detach();
        
        // Attachons maintenant les tags spécifiques
        $quiz->tags()->attach($tagIds);
        
        // Rechargeons la relation pour être sûr d'avoir les données à jour
        $quiz->load('tags');
        
        $this->assertCount(3, $quiz->tags);
        foreach ($tags as $tag) {
            $this->assertTrue($quiz->tags->contains($tag->id));
        }
    }
}
