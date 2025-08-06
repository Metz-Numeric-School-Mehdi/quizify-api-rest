<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    protected $fillable = ["quiz_id", "content", "question_type_id"];
    public function questionType(): BelongsTo
    {
        return $this->belongsTo(QuestionType::class);
    }
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}
