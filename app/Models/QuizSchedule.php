<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizSchedule extends Model
{
    protected $fillable = ["quiz_id", "user_id", "start_time", "end_time"];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
