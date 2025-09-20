<?php

namespace App\Http\Controllers;

use App\Repositories\Answer\AnswerRepository;

class AnswerController extends CRUDController
{
    /**
     * Create a new controller instance.
     *
     * @param AnswerRepository $repository
     */
    public function __construct(AnswerRepository $repository)
    {
        parent::__construct($repository);
    }
}
