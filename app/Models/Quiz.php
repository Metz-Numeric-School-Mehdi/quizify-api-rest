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
     * This method checks if Elasticsearch is available before allowing indexing.
     *
     * @return bool
     */
    public function shouldBeSearchable(): bool
    {
        try {
            // Vérification simple - utilisons une approche qui ne nécessite pas de passage par référence
            if (config('scout.driver') !== 'elasticsearch') {
                return false;
            }

            $elasticsearchHost = env('ELASTICSEARCH_HOST', 'localhost:9200');

            // Essaie une requête HTTP simple
            $contextOptions = [
                'http' => [
                    'method' => 'HEAD',
                    'timeout' => 1,
                    'ignore_errors' => true
                ]
            ];

            // Prépare l'URL et le contexte
            $url = 'http://' . $elasticsearchHost;
            $context = stream_context_create($contextOptions);

            // Essaie d'accéder à ElasticSearch avec un timeout court
            $result = @file_get_contents($url, false, $context);

            // Si on peut accéder au serveur ElasticSearch, on retourne true
            if ($result !== false) {
                return true;
            }

            Log::warning("ElasticSearch unavailable during indexing attempt: {$elasticsearchHost}");
            return false;
        } catch (\Exception $e) {
            Log::error("Error checking ElasticSearch availability: " . $e->getMessage());
            return false;
        }
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
}
