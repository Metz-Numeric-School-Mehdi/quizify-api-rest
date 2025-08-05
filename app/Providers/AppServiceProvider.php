<?php

namespace App\Providers;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\EngineManager;
use Matchish\ScoutElasticSearch\Engines\ElasticSearchEngine;
use App\Services\ElasticsearchService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Client::class, function () {
            return ClientBuilder::create()
                ->setHosts([config('scout.elasticsearch.hosts')[0]])
                ->build();
        });

        $this->app->singleton(ElasticsearchService::class, function ($app) {
            return new ElasticsearchService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enregistrement du driver ElasticSearch pour Laravel Scout
        resolve(EngineManager::class)->extend('elasticsearch', function () {
            return new ElasticSearchEngine(resolve(Client::class));
        });
    }
}
