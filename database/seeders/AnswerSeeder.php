<?php

namespace Database\Seeders;

use App\Models\UserAnswer;
use Database\Factories\AnswerFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AnswerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AnswerFactory::new()->count(20)->create();
    }
}
