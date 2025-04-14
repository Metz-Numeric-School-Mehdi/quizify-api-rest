<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = ["title", "description", "is_public", "user_id"];

    public function quizLevel(): BelongsTo
    {
        return $this->belongsTo(QuizLevel::class);
    }
}
