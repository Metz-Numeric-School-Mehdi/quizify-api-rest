<?php

namespace Tests\Feature;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuestionType;
use App\Models\Answer;
use App\Models\User;
use App\Models\Category;
use App\Models\QuizLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderingQuestionTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $quiz;
    protected $question;
    protected $questionType;
    protected $category;
    protected $quizLevel;

    protected function setUp(): void
    {
        parent::setUp();

        // Utiliser DB::statement pour contourner le problème des timestamps
        DB::statement("INSERT INTO categories (id, name, created_at) VALUES (1, 'Test Category', '" . now() . "')");
        $this->category = Category::find(1);

        $this->quizLevel = QuizLevel::create([
            'name' => 'Test Level',
            'description' => 'Test Description'
        ]);

        $this->user = User::factory()->create();

        // Créer le type de question 'Remise dans l'ordre' s'il n'existe pas
        $this->questionType = QuestionType::firstOrCreate(['name' => 'Remise dans l\'ordre']);

        $this->quiz = Quiz::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'level_id' => $this->quizLevel->id,
        ]);

        $this->question = Question::create([
            'quiz_id' => $this->quiz->id,
            'question_type_id' => $this->questionType->id,
            'content' => 'Remettez ces evenements dans le bon ordre'
        ]);
    }

    /**
     * Test creating an ordering question.
     */
    public function test_create_ordering_question(): void
    {
        $questionData = [
            'quiz_id' => $this->quiz->id,
            'content' => 'Remettez dans le bon ordre les etapes de developpement',
            'answers' => [
                ['content' => 'Analyse', 'order_position' => 1],
                ['content' => 'Conception', 'order_position' => 2],
                ['content' => 'Developpement', 'order_position' => 3],
                ['content' => 'Tests', 'order_position' => 4]
            ]
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/ordering-questions', $questionData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('questions', [
            'content' => 'Remettez dans le bon ordre les etapes de developpement',
            'question_type_id' => $this->questionType->id
        ]);

        $this->assertDatabaseHas('answers', [
            'content' => 'Analyse',
            'order_position' => 1
        ]);
    }

    /**
     * Test getting an ordering question.
     */
    public function test_get_ordering_question(): void
    {
        $orderingQuestion = $this->createOrderingQuestion();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/ordering-questions/{$orderingQuestion->id}");

        $response->assertStatus(200);
    }

    /**
     * Create a test ordering question with answers.
     */
    private function createOrderingQuestion(): Question
    {
        $question = Question::create([
            'quiz_id' => $this->quiz->id,
            'content' => 'Question de test pour remise dans le bon ordre',
            'question_type_id' => $this->questionType->id
        ]);

        $answers = [
            ['content' => 'Premier', 'order_position' => 1],
            ['content' => 'Deuxieme', 'order_position' => 2],
            ['content' => 'Troisieme', 'order_position' => 3],
            ['content' => 'Quatrieme', 'order_position' => 4]
        ];

        foreach ($answers as $answerData) {
            Answer::create([
                'question_id' => $question->id,
                'content' => $answerData['content'],
                'order_position' => $answerData['order_position'],
                'is_correct' => true
            ]);
        }

        return $question;
    }
}
