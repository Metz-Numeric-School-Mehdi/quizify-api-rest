<?php

namespace Database\Seeders;

use App\Models\Answer;
use Illuminate\Database\Seeder;

class AnswerSeeder extends Seeder
{
    public function run(): void
    {
        Answer::create([
            'question_id' => 1,
            'content' => 'php artisan make:controller',
            'is_correct' => true,
            'order_position' => 1,
        ]);
        Answer::create([
            'question_id' => 1,
            'content' => 'php artisan make:model',
            'is_correct' => false,
            'order_position' => 2,
        ]);
        Answer::create([
            'question_id' => 1,
            'content' => 'php artisan make:migration',
            'is_correct' => false,
            'order_position' => 3,
        ]);
        Answer::create([
            'question_id' => 1,
            'content' => 'php artisan create:controller',
            'is_correct' => false,
            'order_position' => 4,
        ]);

        Answer::create([
            'question_id' => 2,
            'content' => '.env',
            'is_correct' => true,
            'order_position' => 1,
        ]);
        Answer::create([
            'question_id' => 2,
            'content' => 'routes/web.php',
            'is_correct' => false,
            'order_position' => 2,
        ]);
        Answer::create([
            'question_id' => 2,
            'content' => 'config/app.php',
            'is_correct' => false,
            'order_position' => 3,
        ]);
        Answer::create([
            'question_id' => 2,
            'content' => 'config/database.php',
            'is_correct' => false,
            'order_position' => 4,
        ]);

        Answer::create([
            'question_id' => 3,
            'content' => 'MVC',
            'is_correct' => true,
            'order_position' => 1,
        ]);
        Answer::create([
            'question_id' => 3,
            'content' => 'MVP',
            'is_correct' => false,
            'order_position' => 2,
        ]);
        Answer::create([
            'question_id' => 3,
            'content' => 'MVVM',
            'is_correct' => false,
            'order_position' => 3,
        ]);
        Answer::create([
            'question_id' => 3,
            'content' => 'MVI',
            'is_correct' => false,
            'order_position' => 4,
        ]);

        Answer::create([
            'question_id' => 4,
            'content' => 'POST',
            'is_correct' => true,
            'order_position' => 1,
        ]);
        Answer::create([
            'question_id' => 4,
            'content' => 'GET',
            'is_correct' => false,
            'order_position' => 2,
        ]);
        Answer::create([
            'question_id' => 4,
            'content' => 'PUT',
            'is_correct' => false,
            'order_position' => 3,
        ]);
        Answer::create([
            'question_id' => 4,
            'content' => 'DELETE',
            'is_correct' => false,
            'order_position' => 4,
        ]);

        Answer::create([
            'question_id' => 5,
            'content' => 'Eloquent',
            'is_correct' => true,
            'order_position' => 1,
        ]);
        Answer::create([
            'question_id' => 5,
            'content' => 'Doctrine',
            'is_correct' => false,
            'order_position' => 2,
        ]);
        Answer::create([
            'question_id' => 5,
            'content' => 'Query Builder',
            'is_correct' => false,
            'order_position' => 3,
        ]);
        Answer::create([
            'question_id' => 5,
            'content' => 'Active Record',
            'is_correct' => false,
            'order_position' => 4,
        ]);

        Answer::create([
            'question_id' => 6,
            'content' => '== compare les valeurs, === compare les valeurs et les types',
            'is_correct' => true,
            'order_position' => 1,
        ]);
        Answer::create([
            'question_id' => 6,
            'content' => '== est plus strict que ===',
            'is_correct' => false,
            'order_position' => 2,
        ]);
        Answer::create([
            'question_id' => 6,
            'content' => 'Aucune différence',
            'is_correct' => false,
            'order_position' => 3,
        ]);
        Answer::create([
            'question_id' => 6,
            'content' => '=== est obsolète',
            'is_correct' => false,
            'order_position' => 4,
        ]);

        Answer::create([
            'question_id' => 7,
            'content' => 'Typage statique optionnel',
            'is_correct' => true,
            'order_position' => 1,
        ]);
        Answer::create([
            'question_id' => 7,
            'content' => 'Typage dynamique uniquement',
            'is_correct' => false,
            'order_position' => 2,
        ]);
        Answer::create([
            'question_id' => 7,
            'content' => 'Typage statique obligatoire',
            'is_correct' => false,
            'order_position' => 3,
        ]);
        Answer::create([
            'question_id' => 7,
            'content' => 'Pas de typage',
            'is_correct' => false,
            'order_position' => 4,
        ]);

        Answer::create([
            'question_id' => 8,
            'content' => 'interface',
            'is_correct' => true,
            'order_position' => 1,
        ]);
        Answer::create([
            'question_id' => 8,
            'content' => 'abstract',
            'is_correct' => false,
            'order_position' => 2,
        ]);
        Answer::create([
            'question_id' => 8,
            'content' => 'implements',
            'is_correct' => false,
            'order_position' => 3,
        ]);
        Answer::create([
            'question_id' => 8,
            'content' => 'extends',
            'is_correct' => false,
            'order_position' => 4,
        ]);

        Answer::create([
            'question_id' => 9,
            'content' => 'const',
            'is_correct' => true,
            'order_position' => 1,
        ]);
        Answer::create([
            'question_id' => 9,
            'content' => 'define',
            'is_correct' => false,
            'order_position' => 2,
        ]);
        Answer::create([
            'question_id' => 9,
            'content' => 'static',
            'is_correct' => false,
            'order_position' => 3,
        ]);
        Answer::create([
            'question_id' => 9,
            'content' => 'final',
            'is_correct' => false,
            'order_position' => 4,
        ]);

        Answer::create([
            'question_id' => 10,
            'content' => 'Réception de la requête',
            'is_correct' => true,
            'order_position' => 1,
        ]);
        Answer::create([
            'question_id' => 10,
            'content' => 'Initialisation de PHP',
            'is_correct' => true,
            'order_position' => 2,
        ]);
        Answer::create([
            'question_id' => 10,
            'content' => 'Exécution du script',
            'is_correct' => true,
            'order_position' => 3,
        ]);
        Answer::create([
            'question_id' => 10,
            'content' => 'Envoi de la réponse',
            'is_correct' => true,
            'order_position' => 4,
        ]);

        Answer::create([
            'question_id' => 11,
            'content' => '() => {}',
            'is_correct' => true,
            'order_position' => 1,
        ]);
        Answer::create([
            'question_id' => 11,
            'content' => 'function() {}',
            'is_correct' => false,
            'order_position' => 2,
        ]);
        Answer::create([
            'question_id' => 11,
            'content' => '() -> {}',
            'is_correct' => false,
            'order_position' => 3,
        ]);
        Answer::create([
            'question_id' => 11,
            'content' => '() => []',
            'is_correct' => false,
            'order_position' => 4,
        ]);

        Answer::create([
            'question_id' => 12,
            'content' => 'let',
            'is_correct' => true,
            'order_position' => 1,
        ]);
        Answer::create([
            'question_id' => 12,
            'content' => 'var',
            'is_correct' => false,
            'order_position' => 2,
        ]);
        Answer::create([
            'question_id' => 12,
            'content' => 'function',
            'is_correct' => false,
            'order_position' => 3,
        ]);
        Answer::create([
            'question_id' => 12,
            'content' => 'scope',
            'is_correct' => false,
            'order_position' => 4,
        ]);

        Answer::create([
            'question_id' => 13,
            'content' => 'const {prop} = object',
            'is_correct' => true,
            'order_position' => 1,
        ]);
        Answer::create([
            'question_id' => 13,
            'content' => 'const prop = object.prop',
            'is_correct' => false,
            'order_position' => 2,
        ]);
        Answer::create([
            'question_id' => 13,
            'content' => 'object.destructure()',
            'is_correct' => false,
            'order_position' => 3,
        ]);
        Answer::create([
            'question_id' => 13,
            'content' => 'Object.extract()',
            'is_correct' => false,
            'order_position' => 4,
        ]);

        Answer::create([
            'question_id' => 14,
            'content' => 'import Module from "./module"',
            'is_correct' => true,
            'order_position' => 1,
        ]);
        Answer::create([
            'question_id' => 14,
            'content' => 'import {Module} from "./module"',
            'is_correct' => false,
            'order_position' => 2,
        ]);
        Answer::create([
            'question_id' => 14,
            'content' => 'require("./module")',
            'is_correct' => false,
            'order_position' => 3,
        ]);
        Answer::create([
            'question_id' => 14,
            'content' => 'include "./module"',
            'is_correct' => false,
            'order_position' => 4,
        ]);

        Answer::create([
            'question_id' => 15,
            'content' => 'pending',
            'is_correct' => true,
            'order_position' => 1,
        ]);
        Answer::create([
            'question_id' => 15,
            'content' => 'fulfilled',
            'is_correct' => true,
            'order_position' => 2,
        ]);
        Answer::create([
            'question_id' => 15,
            'content' => 'rejected',
            'is_correct' => true,
            'order_position' => 3,
        ]);

        Answer::create([
            'question_id' => 16,
            'content' => 'WHERE',
            'is_correct' => true,
            'order_position' => 1,
        ]);
        Answer::create([
            'question_id' => 16,
            'content' => 'SELECT',
            'is_correct' => false,
            'order_position' => 2,
        ]);
        Answer::create([
            'question_id' => 16,
            'content' => 'FROM',
            'is_correct' => false,
            'order_position' => 3,
        ]);
        Answer::create([
            'question_id' => 16,
            'content' => 'ORDER BY',
            'is_correct' => false,
            'order_position' => 4,
        ]);

        Answer::create([
            'question_id' => 17,
            'content' => 'Clé primaire',
            'is_correct' => true,
            'order_position' => 1,
        ]);
        Answer::create([
            'question_id' => 17,
            'content' => 'Clé étrangère',
            'is_correct' => false,
            'order_position' => 2,
        ]);
        Answer::create([
            'question_id' => 17,
            'content' => 'Index',
            'is_correct' => false,
            'order_position' => 3,
        ]);
        Answer::create([
            'question_id' => 17,
            'content' => 'Contrainte',
            'is_correct' => false,
            'order_position' => 4,
        ]);

        Answer::create([
            'question_id' => 18,
            'content' => 'CREATE TABLE',
            'is_correct' => true,
            'order_position' => 1,
        ]);
        Answer::create([
            'question_id' => 18,
            'content' => 'INSERT TABLE',
            'is_correct' => false,
            'order_position' => 2,
        ]);
        Answer::create([
            'question_id' => 18,
            'content' => 'NEW TABLE',
            'is_correct' => false,
            'order_position' => 3,
        ]);
        Answer::create([
            'question_id' => 18,
            'content' => 'ADD TABLE',
            'is_correct' => false,
            'order_position' => 4,
        ]);

        Answer::create([
            'question_id' => 19,
            'content' => 'Retourne les lignes qui ont des correspondances dans les deux tables',
            'is_correct' => true,
            'order_position' => 1,
        ]);
        Answer::create([
            'question_id' => 19,
            'content' => 'Retourne toutes les lignes de la table de gauche',
            'is_correct' => false,
            'order_position' => 2,
        ]);
        Answer::create([
            'question_id' => 19,
            'content' => 'Retourne toutes les lignes des deux tables',
            'is_correct' => false,
            'order_position' => 3,
        ]);
        Answer::create([
            'question_id' => 19,
            'content' => 'Retourne les lignes sans correspondance',
            'is_correct' => false,
            'order_position' => 4,
        ]);

        Answer::create([
            'question_id' => 20,
            'content' => 'Analyser le plan d\'exécution',
            'is_correct' => true,
            'order_position' => 1,
        ]);
        Answer::create([
            'question_id' => 20,
            'content' => 'Identifier les goulots d\'étranglement',
            'is_correct' => true,
            'order_position' => 2,
        ]);
        Answer::create([
            'question_id' => 20,
            'content' => 'Ajouter des index appropriés',
            'is_correct' => true,
            'order_position' => 3,
        ]);
        Answer::create([
            'question_id' => 20,
            'content' => 'Tester les performances',
            'is_correct' => true,
            'order_position' => 4,
        ]);

        Answer::create([
            'question_id' => 21,
            'content' => 'Une fonction ou classe qui retourne du JSX',
            'is_correct' => true,
            'order_position' => 1,
        ]);
        Answer::create([
            'question_id' => 21,
            'content' => 'Un fichier CSS',
            'is_correct' => false,
            'order_position' => 2,
        ]);
        Answer::create([
            'question_id' => 21,
            'content' => 'Une base de données',
            'is_correct' => false,
            'order_position' => 3,
        ]);
        Answer::create([
            'question_id' => 21,
            'content' => 'Un serveur web',
            'is_correct' => false,
            'order_position' => 4,
        ]);

        Answer::create([
            'question_id' => 22,
            'content' => 'Elles sont immutables',
            'is_correct' => true,
            'order_position' => 1,
        ]);
        Answer::create([
            'question_id' => 22,
            'content' => 'Elles sont mutables',
            'is_correct' => false,
            'order_position' => 2,
        ]);
        Answer::create([
            'question_id' => 22,
            'content' => 'Elles sont optionnelles',
            'is_correct' => false,
            'order_position' => 3,
        ]);
        Answer::create([
            'question_id' => 22,
            'content' => 'Elles sont globales',
            'is_correct' => false,
            'order_position' => 4,
        ]);

        Answer::create([
            'question_id' => 23,
            'content' => 'useState',
            'is_correct' => true,
            'order_position' => 1,
        ]);
        Answer::create([
            'question_id' => 23,
            'content' => 'useEffect',
            'is_correct' => false,
            'order_position' => 2,
        ]);
        Answer::create([
            'question_id' => 23,
            'content' => 'useContext',
            'is_correct' => false,
            'order_position' => 3,
        ]);
        Answer::create([
            'question_id' => 23,
            'content' => 'useReducer',
            'is_correct' => false,
            'order_position' => 4,
        ]);

        Answer::create([
            'question_id' => 24,
            'content' => 'function Component() { return <div />; }',
            'is_correct' => true,
            'order_position' => 1,
        ]);
        Answer::create([
            'question_id' => 24,
            'content' => 'class Component extends React.Component',
            'is_correct' => false,
            'order_position' => 2,
        ]);
        Answer::create([
            'question_id' => 24,
            'content' => 'const Component = React.createClass',
            'is_correct' => false,
            'order_position' => 3,
        ]);
        Answer::create([
            'question_id' => 24,
            'content' => 'new React.Component()',
            'is_correct' => false,
            'order_position' => 4,
        ]);

        Answer::create([
            'question_id' => 25,
            'content' => 'Mounting',
            'is_correct' => true,
            'order_position' => 1,
        ]);
        Answer::create([
            'question_id' => 25,
            'content' => 'Updating',
            'is_correct' => true,
            'order_position' => 2,
        ]);
        Answer::create([
            'question_id' => 25,
            'content' => 'Unmounting',
            'is_correct' => true,
            'order_position' => 3,
        ]);
    }
}
