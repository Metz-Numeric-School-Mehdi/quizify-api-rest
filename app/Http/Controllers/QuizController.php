<?php

namespace App\Http\Controllers;

use App\Repositories\Quiz\QuizRepository;
use App\Http\Controllers\CRUDController;

class QuizController extends CRUDController
{
    /**
     * Create a new controller instance.
     *
     * @param QuizRepository $repository
     */
    public function __construct(QuizRepository $repository)
    {
        parent::__construct($repository);
    }
}
