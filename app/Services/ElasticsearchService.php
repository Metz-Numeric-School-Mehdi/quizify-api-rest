<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Laravel\Scout\Searchable;

class ElasticsearchService
{
    /**
     * Safely indexes a model in Elasticsearch.
     * Catches and logs exceptions without interrupting the application flow.
     *
     * @param Model $model The model to index
     * @return bool Whether indexing was successful
     */
    public function safelyIndex(Model $model): bool
    {
        if (!in_array(Searchable::class, class_uses_recursive($model))) {
            return false;
        }

        try {
            if ($model->shouldBeSearchable()) {
                $model->searchable();
                return true;
            }
        } catch (\Exception $e) {
            Log::error("Failed to index model in Elasticsearch: " . $e->getMessage(), [
                'model' => get_class($model),
                'id' => $model->getKey(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return false;
    }

    /**
     * Checks if Elasticsearch is available.
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        try {
            if (config('scout.driver') !== 'elasticsearch') {
                return false;
            }

            $elasticsearchHost = env('ELASTICSEARCH_HOST', 'localhost:9200');

            $contextOptions = [
                'http' => [
                    'method' => 'HEAD',
                    'timeout' => 1,
                    'ignore_errors' => true
                ]
            ];

            $url = 'http://' . $elasticsearchHost;
            $context = stream_context_create($contextOptions);

            $result = @file_get_contents($url, false, $context);

            return ($result !== false);
        } catch (\Exception $e) {
            Log::error("Error checking ElasticSearch availability: " . $e->getMessage());
            return false;
        }
    }
}
