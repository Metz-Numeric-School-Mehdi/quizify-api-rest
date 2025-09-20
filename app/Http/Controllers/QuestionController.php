<?php

namespace App\Http\Controllers;

use App\Repositories\Question\QuestionRepository;
use App\Http\Controllers\CRUDController;

class QuestionController extends CRUDController
{
    /**
     * Create a new controller instance.
     *
     * @param QuestionRepository $repository
     */
    public function __construct(QuestionRepository $repository)
    {
        parent::__construct($repository);
    }
}
