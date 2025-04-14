<?php

namespace Database\Seeders;

use App\Models\QuestionType;
use Illuminate\Database\Seeder;

class QuestionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $questionTypes = [
            ["name" => "Choix multiples"],
            ["name" => "Vrai ou Faux"],
            ["name" => "Choix unique"],
        ];

        foreach ($questionTypes as $questionType) {
            QuestionType::create($questionType);
        }
    }
}
