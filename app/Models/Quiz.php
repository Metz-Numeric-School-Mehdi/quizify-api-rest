<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class Quiz extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Searchable;

    protected $fillable = [
        "title",
        "slug",
        "description",
        "is_public",
        "level_id",
        "status",
        "user_id",
        "duration",
        "pass_score",
        "thumbnail",
        "tag_id",
        "category_id",
    ];

    /**
     * Determines if the model should be searchable.
     *
     * @return bool
     */
    public function shouldBeSearchable(): bool
    {
        return config('scout.driver') === 'elasticsearch' &&
               config('app.env') !== 'testing';
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(QuizLevel::class);
    }

    public function questions()
    {
        return $this->hasMany(\App\Models\Question::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
                        'title' => $this->title,
            'description' => $this->description,
            'level_id' => $this->level_id,
            'category_id' => $this->category_id,
            'status' => $this->status,
            'is_public' => $this->is_public,
            'tags' => $this->tags->pluck('name')->toArray(),
        ];
    }

    /**
     * Accessor pour savoir si le quiz a un temps limité
     */
    public function getIsTimedAttribute(): bool
    {
        return $this->duration !== null;
    }

    /**
     * Accessor pour obtenir la durée formatée
     */
    public function getFormattedDurationAttribute(): string
    {
        if ($this->duration === null) {
            return 'Temps illimité';
        }

        return $this->duration . ' minute' . ($this->duration > 1 ? 's' : '');
    }
}
