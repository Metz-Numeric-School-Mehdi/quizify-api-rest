<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_user()
    {
        $this->seed(RoleSeeder::class);
        $role = Role::where("name", "admin")->first();
        $user = User::factory()->create([
            "role_id" => $role->id,
        ]);

        $this->assertDatabaseHas("users", [
            "id" => $user->id,
            "role_id" => $role->id,
        ]);

        $this->assertInstanceOf(User::class, $user);
    }
}
