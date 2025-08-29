<?php

return [
    'driver' => env('SCOUT_DRIVER', 'elasticsearch'),

    'elasticsearch' => [
        'index_prefix' => env('ELASTICSEARCH_INDEX_PREFIX', 'quizify_'),
        'hosts' => [
            env('ELASTICSEARCH_HOST', 'localhost:9200'),
        ],
        'refresh' => env('ELASTICSEARCH_REFRESH', true),
    ],

    'queue' => false,

    'soft_delete' => true,
];
