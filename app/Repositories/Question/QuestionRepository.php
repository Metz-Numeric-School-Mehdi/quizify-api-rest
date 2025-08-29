<?php

namespace App\Repositories\Question;

use App\Models\Question;
use App\Components\Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class QuestionRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(new Question());
    }

    /**
     * The relations to eager load on every query.
     *
     * @var array
     *
     */
    protected $withRelations = [
        'answers',
        'questionType',
        'quiz'
    ];

    /**
     * Get questions with their relationships.
     *
     * @return Collection
     */
    public function index()
    {
        return $this->model->with($this->withRelations)->get();
    }

    /**
     * Show a specific question with relationships.
     *
     * @param int $id
     * @return Model
     */
    public function show($id)
    {
        return $this->model->with($this->withRelations)->findOrFail($id);
    }
}
