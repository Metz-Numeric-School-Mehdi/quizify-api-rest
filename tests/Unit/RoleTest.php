<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RoleSeeder;

/**
 * Role model unit tests.
 *
 * This test class verifies the core functionality of the Role model,
 * including creation, relationships with users, and CRUD operations.
 */
class RoleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    /**
     * Test creating a role.
     *
     * @test
     */
    public function it_creates_a_role()
    {
        $role = Role::create([
            'name' => 'moderator',
            'description' => 'Moderator role with limited access',
        ]);

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => 'moderator',
            'description' => 'Moderator role with limited access',
        ]);

        $this->assertInstanceOf(Role::class, $role);
    }

    /**
     * Test retrieving seeded roles.
     *
     * @test
     */
    public function it_retrieves_seeded_roles()
    {
        // RoleSeeder should create admin and user roles
        $adminRole = Role::where('name', 'admin')->first();
        $userRole = Role::where('name', 'user')->first();

        $this->assertNotNull($adminRole);
        $this->assertNotNull($userRole);
        $this->assertEquals('admin', $adminRole->name);
        $this->assertEquals('user', $userRole->name);
    }

    /**
     * Test role can have multiple users.
     *
     * @test
     */
    public function it_can_have_multiple_users()
    {
        $role = Role::where('name', 'user')->first();

        $user1 = User::factory()->create([
            'role_id' => $role->id,
        ]);

        $user2 = User::factory()->create([
            'role_id' => $role->id,
        ]);

        // Verify users have the role
        $this->assertEquals($role->id, $user1->role_id);
        $this->assertEquals($role->id, $user2->role_id);

        // Verify role has users
        $usersCount = $role->users()->count();
        $this->assertGreaterThanOrEqual(2, $usersCount);
    }

    /**
     * Test updating role attributes.
     *
     * @test
     */
    public function it_can_update_role_attributes()
    {
        $role = Role::create([
            'name' => 'original_role',
            'description' => 'Original role description',
        ]);

        $role->update([
            'name' => 'updated_role',
            'description' => 'Updated role description',
        ]);

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => 'updated_role',
            'description' => 'Updated role description',
        ]);
    }

    /**
     * Test role name is required.
     *
     * @test
     */
    public function it_requires_name_field()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Role::create([
            'description' => 'Role without name',
        ]);
    }

    /**
     * Test finding role by name.
     *
     * @test
     */
    public function it_can_find_role_by_name()
    {
        $role = Role::create([
            'name' => 'custom_role',
            'description' => 'Custom role for testing',
        ]);

        $found = Role::where('name', 'custom_role')->first();

        $this->assertNotNull($found);
        $this->assertEquals($role->id, $found->id);
        $this->assertEquals('custom_role', $found->name);
    }

    /**
     * Test role name should be unique.
     *
     * @test
     */
    public function it_enforces_unique_role_names()
    {
        Role::create([
            'name' => 'unique_role',
            'description' => 'First role with this name',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Role::create([
            'name' => 'unique_role',
            'description' => 'Second role with same name',
        ]);
    }
}
