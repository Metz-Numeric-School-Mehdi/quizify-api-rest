<?php

namespace Database\Seeders;

use App\Models\Badge;
use Database\Factories\BadgeFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BadgeFactory::new()->count(10)->create();
    }
}
