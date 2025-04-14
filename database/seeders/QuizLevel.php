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
            ["name" => "Easy", "created_at" => now()],
            ["name" => "Medium", "created_at" => now()],
            ["name" => "Hard", "created_at" => now()],
        ]);
    }
}
