<?php

namespace Database\Seeders;

use Database\Factories\UserBadgeFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserBadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserBadgeFactory::new()->count(10)->create();
    }
}
