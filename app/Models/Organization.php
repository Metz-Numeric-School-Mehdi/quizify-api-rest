<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $fillable = ["name"];
    
    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
