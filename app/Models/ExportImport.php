<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExportImport extends Model
{
    protected $fillable = ["type", "entity", "file_path", "user_id"];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
