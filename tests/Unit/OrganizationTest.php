<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RoleSeeder;

/**
 * Organization model unit tests.
 *
 * This test class verifies the core functionality of the Organization model,
 * including creation, relationships with teams and users, and CRUD operations.
 */
class OrganizationTest extends TestCase
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
     * Test creating an organization.
     *
     * @test
     */
    public function it_creates_an_organization()
    {
        $organization = Organization::create([
            'name' => 'Test Organization',
            'description' => 'A test organization for unit testing',
        ]);

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'name' => 'Test Organization',
            'description' => 'A test organization for unit testing',
        ]);

        $this->assertInstanceOf(Organization::class, $organization);
    }

    /**
     * Test organization can have multiple teams.
     *
     * @test
     */
    public function it_can_have_multiple_teams()
    {
        $organization = Organization::create([
            'name' => 'Multi-Team Organization',
            'description' => 'Organization with multiple teams',
        ]);

        $team1 = Team::create([
            'name' => 'Development Team',
            'description' => 'Software development team',
            'organization_id' => $organization->id,
        ]);

        $team2 = Team::create([
            'name' => 'QA Team',
            'description' => 'Quality assurance team',
            'organization_id' => $organization->id,
        ]);

        // Verify teams belong to organization
        $this->assertEquals($organization->id, $team1->organization_id);
        $this->assertEquals($organization->id, $team2->organization_id);

        // Verify organization has teams
        $teamsCount = $organization->teams()->count();
        $this->assertEquals(2, $teamsCount);
    }

    /**
     * Test organization can have multiple users.
     *
     * @test
     */
    public function it_can_have_multiple_users()
    {
        $organization = Organization::create([
            'name' => 'User Organization',
            'description' => 'Organization with users',
        ]);

        $user1 = User::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $user2 = User::factory()->create([
            'organization_id' => $organization->id,
        ]);

        // Verify users belong to organization
        $this->assertEquals($organization->id, $user1->organization_id);
        $this->assertEquals($organization->id, $user2->organization_id);

        // Verify organization has users
        $usersCount = $organization->users()->count();
        $this->assertEquals(2, $usersCount);
    }

    /**
     * Test updating organization attributes.
     *
     * @test
     */
    public function it_can_update_organization_attributes()
    {
        $organization = Organization::create([
            'name' => 'Original Name',
            'description' => 'Original description',
        ]);

        $organization->update([
            'name' => 'Updated Organization Name',
            'description' => 'Updated organization description',
        ]);

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'name' => 'Updated Organization Name',
            'description' => 'Updated organization description',
        ]);
    }

    /**
     * Test organization validation requirements.
     *
     * @test
     */
    public function it_requires_name_field()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Organization::create([
            'description' => 'Organization without name',
        ]);
    }

    /**
     * Test finding organization by name.
     *
     * @test
     */
    public function it_can_find_organization_by_name()
    {
        $organization = Organization::create([
            'name' => 'Unique Organization Name',
            'description' => 'Searchable organization',
        ]);

        $found = Organization::where('name', 'Unique Organization Name')->first();

        $this->assertNotNull($found);
        $this->assertEquals($organization->id, $found->id);
        $this->assertEquals('Unique Organization Name', $found->name);
    }
}
