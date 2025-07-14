<?php

namespace Tests\Unit;

use App\Models\Answer;
use App\Models\Question;
use App\Models\QuestionType;
use App\Models\Quiz;
use App\Models\QuizLevel;
use App\Models\Category;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\QuestionTypeSeeder;
use Database\Seeders\QuizLevelSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Answer model unit tests.
 * 
 * This test class verifies the core functionality of the Answer model,
 * including creation, relationships, updates, and soft deletion.
 */
class AnswerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     * 
     * Seeds the database with necessary data for testing answer functionality,
     * including roles, categories, question types, quiz levels, and users.
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
            UserSeeder::class,
        ]);
    }

    /**
     * Test the creation of an answer and verify its relationship with a question.
     * 
     * This test creates all necessary related models (quiz, question) and verifies
     * that the answer's relationship to its parent question is properly established.
     *
     * @test
     * @return void
     */
    public function it_creates_an_answer_and_checks_relation()
    {
        $questionType = QuestionType::first();
        $quizLevel = QuizLevel::first();
        $user = User::first();
        $category = Category::first();

        $quiz = Quiz::create([
            "title" => "Quiz Laravel",
            "slug" => "quiz-laravel",
            "description" => "Testez vos connaissances sur Laravel.",
            "is_public" => true,
            "level_id" => $quizLevel->id,
            "status" => "published",
            "user_id" => $user->id,
            "duration" => 30,
            "pass_score" => 70,
            "thumbnail" => null,
            "category_id" => $category->id,
        ]);

        $question = Question::create([
            "quiz_id" => $quiz->id,
            "question_type_id" => $questionType->id,
            "content" => "What is Laravel?",
        ]);

        $answer = Answer::create([
            "question_id" => $question->id,
            "content" => "Sample answer",
            "is_correct" => true,
        ]);

        $this->assertDatabaseHas("answers", [
            "id" => $answer->id,
            "question_id" => $question->id,
            "content" => "Sample answer",
            "is_correct" => true,
        ]);

        $this->assertInstanceOf(Question::class, $answer->question);
        $this->assertEquals($question->id, $answer->question->id);
    }

    /**
     * Test the creation of multiple answers using the factory.
     * 
     * This test verifies that the Answer factory can create multiple answers
     * for the same question and checks that the relationships are properly maintained.
     *
     * @test
     * @return void
     */
    public function it_can_create_multiple_answers_using_factory()
    {
        $questionType = QuestionType::first();
        $quizLevel = QuizLevel::first();
        $user = User::first();
        $category = Category::first();

        $quiz = Quiz::create([
            "title" => "Quiz Factory Test",
            "slug" => "quiz-factory-test",
            "description" => "Testing answer factory",
            "is_public" => true,
            "level_id" => $quizLevel->id,
            "status" => "published",
            "user_id" => $user->id,
            "duration" => 20,
            "pass_score" => 60,
            "category_id" => $category->id,
        ]);

        $question = Question::create([
            "quiz_id" => $quiz->id,
            "question_type_id" => $questionType->id,
            "content" => "Test question for multiple answers",
        ]);

        // Create multiple answers for the same question
        $answers = Answer::factory()
            ->count(4)
            ->create([
                "question_id" => $question->id,
            ]);

        $this->assertCount(4, $answers);
        $this->assertDatabaseCount("answers", 4);

        // Verify that all answers belong to the same question
        foreach ($answers as $answer) {
            $this->assertEquals($question->id, $answer->question_id);
        }
    }

    /**
     * Test the soft delete functionality of the Answer model.
     * 
     * This test creates an answer, deletes it, and verifies that it was
     * soft deleted (not removed from the database, but marked with a deletion timestamp).
     *
     * @test
     * @return void
     */
    public function it_can_soft_delete_an_answer()
    {
        $user = User::first();
        $category = Category::first();
        $quizLevel = QuizLevel::first();
        $questionType = QuestionType::first();

        $quiz = Quiz::create([
            "title" => "Quiz for Delete Test",
            "slug" => "quiz-delete-test",
            "description" => "Testing soft delete",
            "is_public" => true,
            "level_id" => $quizLevel->id,
            "status" => "draft",
            "user_id" => $user->id,
            "duration" => 15,
            "pass_score" => 50,
            "category_id" => $category->id,
        ]);

        $question = Question::create([
            "quiz_id" => $quiz->id,
            "question_type_id" => $questionType->id,
            "content" => "Test question for soft delete",
        ]);

        $answer = Answer::factory()->create([
            "question_id" => $question->id,
        ]);

        $answerId = $answer->id;
        $answer->delete();

        // The record should no longer be fetchable
        $this->assertSoftDeleted("answers", ["id" => $answerId]);

        // But it should still exist in the database
        $this->assertDatabaseHas("answers", [
            "id" => $answerId,
            "deleted_at" => now(),
        ]);
    }

    /**
     * Test updating an answer's attributes.
     * 
     * This test creates an answer with initial values, updates those values,
     * and verifies that the changes were properly persisted to the database.
     *
     * @test
     * @return void
     */
    public function it_can_update_an_answer()
    {
        $user = User::first();
        $category = Category::first();
        $quizLevel = QuizLevel::first();
        $questionType = QuestionType::first();

        $quiz = Quiz::create([
            "title" => "Quiz for Update Test",
            "slug" => "quiz-update-test",
            "description" => "Testing answer update",
            "is_public" => true,
            "level_id" => $quizLevel->id,
            "status" => "draft",
            "user_id" => $user->id,
            "duration" => 15,
            "pass_score" => 50,
            "category_id" => $category->id,
        ]);

        $question = Question::create([
            "quiz_id" => $quiz->id,
            "question_type_id" => $questionType->id,
            "content" => "Test question for answer update",
        ]);

        $answer = Answer::factory()->create([
            "question_id" => $question->id,
            "content" => "Original answer",
            "is_correct" => false,
        ]);

        $answer->update([
            "content" => "Updated answer",
            "is_correct" => 1,
        ]);

        $this->assertDatabaseHas("answers", [
            "id" => $answer->id,
            "content" => "Updated answer",
            "is_correct" => 1,
        ]);

        $refreshedAnswer = $answer->fresh();
        $this->assertEquals("Updated answer", $refreshedAnswer->content);
        $this->assertEquals(1, $refreshedAnswer->is_correct);
    }
}
