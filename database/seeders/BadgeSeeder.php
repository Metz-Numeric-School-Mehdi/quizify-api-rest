<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Badge;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        Badge::create([
            "name" => "Débutant",
            "description" => "Attribué après avoir réussi un premier quiz.",
            "icon" => "debutant.png",
        ]);

        Badge::create([
            "name" => "Assidu",
            "description" => "Attribué après avoir terminé 5 quiz.",
            "icon" => "assidu.png",
        ]);
    }
}
