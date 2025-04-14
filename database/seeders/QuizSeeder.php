<?php

namespace Database\Seeders;

use App\Models\Quiz;
use Database\Factories\QuizFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        QuizFactory::new()->count(10)->create();
    }
}
