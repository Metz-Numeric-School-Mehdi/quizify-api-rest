<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = User::factory(20)->create();

        $rank = 1;

        foreach ($users as $user) {
            $user->ranking = $rank++;
            $user->save();
        }

        $adminDetails = [
            "username" => env("ADMIN_USERNAME", "default_username"),
            "firstname" => env("ADMIN_FIRSTNAME", "default_firstname"),
            "lastname" => env("ADMIN_LASTNAME", "default_lastname"),
            "email" => env("ADMIN_EMAIL", "default_email"),
            "password" => bcrypt(env("ADMIN_PASSWORD", "default_password")),
            "role_id" => 1,
        ];

        User::create($adminDetails);

        echo "\nAdmin user created with the following details:\n\n";
        foreach ($adminDetails as $key => $value) {
            echo "$key: $value\n";
        }
    }
}
