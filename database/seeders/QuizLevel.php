<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuizLevel extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table("quiz_levels")->insert([
            ["name" => "Facile", "created_at" => now()],
            ["name" => "Moyen", "created_at" => now()],
            ["name" => "Difficile", "created_at" => now()],
        ]);
    }
}
