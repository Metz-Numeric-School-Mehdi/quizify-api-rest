<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Score extends Model
{
    use SoftDeletes;

    protected $fillable = [
        "user_id",
        "quiz_id",
        "score",
        "total_questions",
        "correct_answers",
        "time_taken"
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}
