<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::insert([
            ["name" => "Sciences", "created_at" => now()],
            ["name" => "Histoire", "created_at" => now()],
            ["name" => "Géographie", "created_at" => now()],
            ["name" => "Littérature", "created_at" => now()],
            ["name" => "Cinéma", "created_at" => now()],
            ["name" => "Musique", "created_at" => now()],
            ["name" => "Sport", "created_at" => now()],
        ]);
    }
}
