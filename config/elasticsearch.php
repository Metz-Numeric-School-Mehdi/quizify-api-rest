<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ElasticSearch Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration for the ElasticSearch service.
    |
    */
    'hosts' => [
        env('ELASTICSEARCH_HOST', 'localhost:9200'),
    ],

    'index_prefix' => env('ELASTICSEARCH_INDEX_PREFIX', 'quizify_'),

    'refresh_index' => env('ELASTICSEARCH_REFRESH', true),

    'ssl_verification' => env('ELASTICSEARCH_SSL_VERIFICATION', false),

    'user' => env('ELASTICSEARCH_USER'),

    'password' => env('ELASTICSEARCH_PASSWORD'),

    'elastic_cloud_id' => env('ELASTIC_CLOUD_ID'),

    'api_key' => env('ELASTIC_API_KEY'),
];
