<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionResponse extends Model
{
    protected $fillable = [
        'quiz_id',
        'user_id',
        'question_id',
        'answer_id',
        'user_answer',
        'user_response_data',
        'is_correct',
        'points',
        'response_time',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function answer()
    {
        return $this->belongsTo(Answer::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
