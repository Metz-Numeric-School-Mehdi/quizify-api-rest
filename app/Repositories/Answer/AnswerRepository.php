<?php

namespace App\Repositories\Answer;

use App\Components\Repository;
use App\Models\Answer;
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
}
