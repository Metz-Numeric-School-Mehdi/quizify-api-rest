<?php

namespace App\Console\Commands;

use App\Models\Quiz;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ReindexElasticsearch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:reindex {--model=* : Model classes to reindex}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reindexer tous les modèles dans Elasticsearch';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $models = $this->option('model');

        if (empty($models)) {
            $models = [Quiz::class];
        }

        foreach ($models as $modelClass) {
            if (!class_exists($modelClass)) {
                $this->error("Le modèle {$modelClass} n'existe pas.");
                continue;
            }

            $this->info("Réindexation de {$modelClass}...");

            try {
                Artisan::call('scout:import', [
                    'searchable' => [$modelClass]
                ]);
                $this->info("Réindexation de {$modelClass} réussie.");
            } catch (\Exception $e) {
                $this->error("Échec de la réindexation de {$modelClass}: " . $e->getMessage());
            }
        }

        $this->info('Réindexation terminée !');
    }
}
