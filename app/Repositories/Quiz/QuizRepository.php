<?php

namespace App\Repositories\Quiz;

use App\Models\Quiz;
use App\Components\Repository;

class QuizRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(new Quiz());
    }
}
