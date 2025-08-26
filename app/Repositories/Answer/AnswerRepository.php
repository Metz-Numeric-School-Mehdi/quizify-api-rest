<?php

namespace App\Repositories\Answer;

use App\Components\Repository;
use App\Models\Answer;
use App\Models\Question;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class AnswerRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(new Answer());
    }

    /**
     * Get questions with their relationships.
     *
     * @return Collection
     */
    public function index()
    {
        return $this->model->with("question")->get();
    }

    /**
     * Show a specific question with relationships.
     *
     * @param int $id
     * @return Model
     */
    public function show($id)
    {
        return $this->model->with("question")->findOrFail($id);
    }

    /**
     * Store answers for a given question from payload:
     * {
     *   question_id: int,
     *   answers: [ { content: string, is_correct: bool }, ... ]
     * }
     *
     * Returns the first created Answer model to respect parent signature.
     *
     * @param array $data
     * @return Model
     */
    public function store(array $data): Model
    {
        $questionId = data_get($data, 'question_id');
        $answers = data_get($data, 'answers', []);

        if (empty($questionId) || !is_array($answers) || empty($answers)) {
            throw new \InvalidArgumentException('Payload invalide: question_id et un tableau answers non vide sont requis.');
        }

        $question = Question::findOrFail($questionId);

        $created = DB::transaction(function () use ($question, $answers) {
            $prepared = collect($answers)
                ->map(function ($answer) {
                    return [
                        'content' => (string) data_get($answer, 'content'),
                        'is_correct' => (bool) data_get($answer, 'is_correct', false),
                    ];
                })
                ->all();

            return $question->answers()->createMany($prepared);
        });

        return $created[0];
    }
}
