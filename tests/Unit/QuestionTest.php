<?php

namespace Tests\Unit;

use App\Models\Answer;
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
 * Question model unit tests.
 * 
 * This test class verifies the core functionality of the Question model,
 * including creation, relationships, updates, and integrations with answers.
 */
class QuestionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     * 
     * Seeds the database with necessary data for testing question functionality,
     * including question types, categories, and quiz levels.
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
     * Test the creation of a question with its relationships.
     * 
     * Verifies that a question can be created with proper associations
     * to quiz and question type.
     *
     * @test
     * @return void
     */
    public function it_creates_a_question_with_proper_relationships()
    {
        $user = User::factory()->create();
        $category = Category::first();
        $quizLevel = QuizLevel::first();
        $questionType = QuestionType::first();
        
        $quiz = Quiz::create([
            "title" => "Test Quiz for Question",
            "slug" => "test-quiz-question",
            "description" => "Quiz for testing question relationships",
            "is_public" => true,
            "level_id" => $quizLevel->id,
            "status" => "published",
            "user_id" => $user->id,
            "duration" => 30,
            "pass_score" => 70,
            "category_id" => $category->id,
        ]);
        
        $question = Question::create([
            "quiz_id" => $quiz->id,
            "question_type_id" => $questionType->id,
            "content" => "What is Laravel?",
        ]);
        
        $this->assertDatabaseHas("questions", [
            "id" => $question->id,
            "quiz_id" => $quiz->id,
            "question_type_id" => $questionType->id,
            "content" => "What is Laravel?",
        ]);
        
        $this->assertInstanceOf(Quiz::class, $question->quiz);
        $this->assertEquals($quiz->id, $question->quiz->id);
        
        $this->assertInstanceOf(QuestionType::class, $question->questionType);
        $this->assertEquals($questionType->id, $question->questionType->id);
    }
    
    /**
     * Test the relationship between questions and answers.
     * 
     * Verifies that answers can be added to questions and properly retrieved.
     *
     * @test
     * @return void
     */
    public function it_can_have_multiple_answers()
    {
        $user = User::factory()->create();
        $quiz = Quiz::create([
            "title" => "Quiz with Questions and Answers",
            "slug" => "quiz-questions-answers",
            "description" => "Testing questions with multiple answers",
            "is_public" => true,
            "level_id" => QuizLevel::first()->id,
            "status" => "published",
            "user_id" => $user->id,
            "duration" => 15,
            "pass_score" => 60,
            "category_id" => Category::first()->id,
        ]);
        
        $question = Question::create([
            "quiz_id" => $quiz->id,
            "question_type_id" => QuestionType::first()->id,
            "content" => "What is PHP?",
        ]);
        
        // Create multiple answers for the question
        $answers = [];
        $answers[] = Answer::create([
            "question_id" => $question->id,
            "content" => "A programming language",
            "is_correct" => true,
        ]);
        
        $answers[] = Answer::create([
            "question_id" => $question->id,
            "content" => "A database system",
            "is_correct" => false,
        ]);
        
        $answers[] = Answer::create([
            "question_id" => $question->id,
            "content" => "A web server",
            "is_correct" => false,
        ]);
        
        $this->assertCount(3, $question->answers);
        
        // Verify the correct answer
        $correctAnswers = $question->answers->where('is_correct', true);
        $this->assertCount(1, $correctAnswers);
        $this->assertEquals("A programming language", $correctAnswers->first()->content);
    }
    
    /**
     * Test updating a question's attributes.
     * 
     * Verifies that a question's content and other attributes can be updated.
     *
     * @test
     * @return void
     */
    public function it_can_update_question_content()
    {
        $user = User::factory()->create();
        $quiz = Quiz::create([
            "title" => "Quiz for Question Update",
            "slug" => "quiz-question-update",
            "description" => "Testing question updates",
            "is_public" => true,
            "level_id" => QuizLevel::first()->id,
            "status" => "draft",
            "user_id" => $user->id,
            "duration" => 10,
            "pass_score" => 50,
            "category_id" => Category::first()->id,
        ]);
        
        $question = Question::create([
            "quiz_id" => $quiz->id,
            "question_type_id" => QuestionType::first()->id,
            "content" => "Original question content",
        ]);
        
        $question->update([
            "content" => "Updated question content",
        ]);
        
        $this->assertDatabaseHas("questions", [
            "id" => $question->id,
            "content" => "Updated question content",
        ]);
        
        $refreshedQuestion = $question->fresh();
        $this->assertEquals("Updated question content", $refreshedQuestion->content);
    }
    
    /**
     * Test changing a question's type.
     * 
     * Verifies that a question can be reassigned to a different question type.
     *
     * @test
     * @return void
     */
    public function it_can_change_question_type()
    {
        $user = User::factory()->create();
        $quiz = Quiz::create([
            "title" => "Quiz for Question Type Change",
            "slug" => "quiz-question-type-change",
            "description" => "Testing changing question types",
            "is_public" => true,
            "level_id" => QuizLevel::first()->id,
            "status" => "draft",
            "user_id" => $user->id,
            "duration" => 10,
            "pass_score" => 50,
            "category_id" => Category::first()->id,
        ]);
        
        $mcqType = QuestionType::where('name', 'Choix multiples')->first();
        $trueFalseType = QuestionType::where('name', 'Vrai ou Faux')->first();
        
        $question = Question::create([
            "quiz_id" => $quiz->id,
            "question_type_id" => $mcqType->id,
            "content" => "This was originally a multiple choice question",
        ]);
        
        $this->assertEquals($mcqType->id, $question->question_type_id);
        
        // Change question type
        $question->update([
            "question_type_id" => $trueFalseType->id,
        ]);
        
        $refreshedQuestion = $question->fresh();
        $this->assertEquals($trueFalseType->id, $refreshedQuestion->question_type_id);
        $this->assertEquals($trueFalseType->id, $refreshedQuestion->questionType->id);
    }
    
    /**
     * Test fetching questions by quiz.
     * 
     * Verifies that we can retrieve all questions belonging to a specific quiz.
     *
     * @test
     * @return void
     */
    public function it_can_retrieve_questions_by_quiz()
    {
        $user = User::factory()->create();
        $quiz = Quiz::create([
            "title" => "Quiz with Multiple Questions",
            "slug" => "quiz-multiple-questions",
            "description" => "Quiz for testing question retrieval",
            "is_public" => true,
            "level_id" => QuizLevel::first()->id,
            "status" => "published",
            "user_id" => $user->id,
            "duration" => 20,
            "pass_score" => 60,
            "category_id" => Category::first()->id,
        ]);
        
        // Create 5 questions for this quiz
        for ($i = 1; $i <= 5; $i++) {
            Question::create([
                "quiz_id" => $quiz->id,
                "question_type_id" => QuestionType::first()->id,
                "content" => "Question {$i} for this quiz",
            ]);
        }
        
        // Create a different quiz with 2 questions
        $anotherQuiz = Quiz::create([
            "title" => "Another Quiz",
            "slug" => "another-quiz",
            "description" => "Another quiz for comparison",
            "is_public" => true,
            "level_id" => QuizLevel::first()->id,
            "status" => "published",
            "user_id" => $user->id,
            "duration" => 10,
            "pass_score" => 50,
            "category_id" => Category::first()->id,
        ]);
        
        for ($i = 1; $i <= 2; $i++) {
            Question::create([
                "quiz_id" => $anotherQuiz->id,
                "question_type_id" => QuestionType::first()->id,
                "content" => "Question {$i} for another quiz",
            ]);
        }
        
        // Verify question count for first quiz
        $this->assertCount(5, $quiz->questions);
        
        // Verify question count for second quiz
        $this->assertCount(2, $anotherQuiz->questions);
        
        // Verify total questions in database
        $this->assertEquals(7, Question::count());
    }
}