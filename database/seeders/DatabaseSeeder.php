<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Download;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            TagSeeder::class,
            CategorySeeder::class,
            QuizLevel::class,
            UserSeeder::class,
            QuizSeeder::class,
            QuestionTypeSeeder::class,
            QuestionSeeder::class,
            AnswerSeeder::class,
            BadgeSeeder::class,
            UserBadgeSeeder::class,
        ]);
    }
}
