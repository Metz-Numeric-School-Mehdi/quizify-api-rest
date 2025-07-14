<?php

namespace Tests\Unit;

use App\Models\Question;
use App\Models\QuestionType;
use App\Models\Quiz;
use App\Models\QuizLevel;
use App\Models\User;
use App\Models\Category;
use Database\Seeders\CategorySeeder;
use Database\Seeders\QuestionTypeSeeder;
use Database\Seeders\QuizLevelSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * QuestionType model unit tests.
 * 
 * This test class verifies the core functionality of the QuestionType model,
 * including creation, relationships with questions, and updates.
 */
class QuestionTypeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     * 
     * Seeds the database with necessary data for testing question type functionality.
     * 
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            RoleSeeder::class,
            CategorySeeder::class,
            QuestionTypeSeeder::class,
            QuizLevelSeeder::class,
        ]);
    }

    /**
     * Test the creation of a question type.
     * 
     * Verifies that a question type can be created with a name.
     *
     * @test
     * @return void
     */
    public function it_creates_a_question_type()
    {
        $questionType = QuestionType::create([
            "name" => "Matching"
        ]);

        $this->assertDatabaseHas("question_types", [
            "id" => $questionType->id,
            "name" => "Matching",
        ]);

        $this->assertInstanceOf(QuestionType::class, $questionType);
    }

    /**
     * Test retrieving seeded question types.
     * 
     * Verifies that the question type seeder correctly populates the database.
     *
     * @test
     * @return void
     */
    public function it_retrieves_seeded_question_types()
    {
        $questionTypes = QuestionType::all();
        
        $this->assertGreaterThanOrEqual(3, $questionTypes->count());
        
        // Check for specific seeded question types
        $this->assertDatabaseHas('question_types', [
            'name' => 'Choix multiples',
        ]);
        
        $this->assertDatabaseHas('question_types', [
            'name' => 'Vrai ou Faux',
        ]);
        
        $this->assertDatabaseHas('question_types', [
            'name' => 'Choix unique',
        ]);
    }

    /**
     * Test updating a question type's name.
     * 
     * Verifies that a question type's name can be updated.
     *
     * @test
     * @return void
     */
    public function it_can_update_question_type_name()
    {
        $questionType = QuestionType::create([
            "name" => "Original Type Name"
        ]);
        
        $questionType->update([
            "name" => "Updated Type Name"
        ]);
        
        $this->assertDatabaseHas("question_types", [
            "id" => $questionType->id,
            "name" => "Updated Type Name",
        ]);
        
        $refreshedType = $questionType->fresh();
        $this->assertEquals("Updated Type Name", $refreshedType->name);
    }

    /**
     * Test relationship between question types and questions.
     * 
     * Verifies that questions can be associated with a question type and retrieved correctly.
     *
     * @test
     * @return void
     */
    public function it_can_have_multiple_questions()
    {
        $user = User::factory()->create();
        $quiz = Quiz::create([
            "title" => "Quiz for Question Types",
            "slug" => "quiz-question-types",
            "description" => "Testing question types relationship",
            "is_public" => true,
            "level_id" => QuizLevel::first()->id,
            "status" => "published",
            "user_id" => $user->id,
            "duration" => 15,
            "pass_score" => 60,
            "category_id" => Category::first()->id,
        ]);
        
        $questionType = QuestionType::where('name', 'Choix multiples')->first();
        
        // Create multiple questions of this type
        for ($i = 1; $i <= 3; $i++) {
            Question::create([
                "quiz_id" => $quiz->id,
                "question_type_id" => $questionType->id,
                "content" => "Multiple choice question {$i}",
            ]);
        }
        
        $this->assertCount(3, $questionType->questions);
        
        // Verify each question belongs to this type
        foreach ($questionType->questions as $question) {
            $this->assertEquals($questionType->id, $question->question_type_id);
        }
    }

    /**
     * Test finding a question type by name.
     * 
     * Verifies that question types can be found by their name.
     *
     * @test
     * @return void
     */
    public function it_can_find_question_type_by_name()
    {
        // Find a known question type
        $mcqType = QuestionType::where('name', 'Choix multiples')->first();
        
        $this->assertNotNull($mcqType);
        $this->assertEquals('Choix multiples', $mcqType->name);
    }
    
    /**
     * Test querying for questions by type.
     * 
     * Verifies that we can get all questions of a specific type.
     *
     * @test
     * @return void
     */
    public function it_can_query_questions_by_type()
    {
        $user = User::factory()->create();
        $quiz = Quiz::create([
            "title" => "Quiz for Question Types Query",
            "slug" => "quiz-question-types-query",
            "description" => "Testing question type queries",
            "is_public" => true,
            "level_id" => QuizLevel::first()->id,
            "status" => "published",
            "user_id" => $user->id,
            "duration" => 15,
            "pass_score" => 60,
            "category_id" => Category::first()->id,
        ]);
        
        $trueFalseType = QuestionType::where('name', 'Vrai ou Faux')->first();
        $mcqType = QuestionType::where('name', 'Choix multiples')->first();
        
        // Create true/false questions
        for ($i = 1; $i <= 2; $i++) {
            Question::create([
                "quiz_id" => $quiz->id,
                "question_type_id" => $trueFalseType->id,
                "content" => "True/False question {$i}",
            ]);
        }
        
        // Create MCQ questions
        for ($i = 1; $i <= 3; $i++) {
            Question::create([
                "quiz_id" => $quiz->id,
                "question_type_id" => $mcqType->id,
                "content" => "MCQ question {$i}",
            ]);
        }
        
        // Query questions by type
        $trueFalseQuestions = Question::where('question_type_id', $trueFalseType->id)->get();
        $mcqQuestions = Question::where('question_type_id', $mcqType->id)->get();
        
        $this->assertCount(2, $trueFalseQuestions);
        $this->assertCount(3, $mcqQuestions);
        
        // Ensure they all belong to the correct type
        foreach ($trueFalseQuestions as $question) {
            $this->assertEquals($trueFalseType->id, $question->question_type_id);
            $this->assertTrue(str_contains($question->content, 'True/False'));
        }
        
        foreach ($mcqQuestions as $question) {
            $this->assertEquals($mcqType->id, $question->question_type_id);
            $this->assertTrue(str_contains($question->content, 'MCQ'));
        }
    }
}