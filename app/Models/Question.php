<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    use SoftDeletes;

    protected $fillable = ["content", "quiz_id", "type_id", "level_id"];

    public function questionTypes(): BelongsTo
    {
        return $this->belongsTo(QuestionType::class);
    }

    public function quizzes(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}
