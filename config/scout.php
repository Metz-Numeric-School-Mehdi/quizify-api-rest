<?php

return [
    // Driver par défaut
    'driver' => env('SCOUT_DRIVER', 'elasticsearch'),

    // Configuration Elasticsearch
    'elasticsearch' => [
        'index_prefix' => env('ELASTICSEARCH_INDEX_PREFIX', 'quizify_'),
        'hosts' => [
            env('ELASTICSEARCH_HOST', 'localhost:9200'),
        ],
        'refresh' => env('ELASTICSEARCH_REFRESH', true),
    ],

    // Désactiver les files d'attente
    'queue' => false,

    // Soft Delete
    'soft_delete' => true,
];
