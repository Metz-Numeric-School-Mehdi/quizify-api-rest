<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Team;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RoleSeeder;

/**
 * Team model unit tests.
 *
 * This test class verifies the core functionality of the Team model,
 * including creation, relationships with organizations and users, and CRUD operations.
 */
class TeamTest extends TestCase
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
     * Test creating a team with organization.
     *
     * @test
     */
    public function it_creates_a_team_with_organization()
    {
        $organization = Organization::create([
            'name' => 'Test Organization',
            'description' => 'Organization for team testing',
        ]);

        $team = Team::create([
            'name' => 'Development Team',
            'description' => 'Software development team',
            'organization_id' => $organization->id,
        ]);

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'name' => 'Development Team',
            'organization_id' => $organization->id,
        ]);

        $this->assertInstanceOf(Team::class, $team);
        $this->assertInstanceOf(Organization::class, $team->organization);
        $this->assertEquals($organization->id, $team->organization_id);
    }

    /**
     * Test team can have multiple users.
     *
     * @test
     */
    public function it_can_have_multiple_users()
    {
        $organization = Organization::create([
            'name' => 'User Organization',
            'description' => 'Organization with team and users',
        ]);

        $team = Team::create([
            'name' => 'QA Team',
            'description' => 'Quality assurance team',
            'organization_id' => $organization->id,
        ]);

        $user1 = User::factory()->create([
            'team_id' => $team->id,
            'organization_id' => $organization->id,
        ]);

        $user2 = User::factory()->create([
            'team_id' => $team->id,
            'organization_id' => $organization->id,
        ]);

        // Verify users belong to team
        $this->assertEquals($team->id, $user1->team_id);
        $this->assertEquals($team->id, $user2->team_id);

        // Verify team has users
        $usersCount = $team->users()->count();
        $this->assertEquals(2, $usersCount);
    }

    /**
     * Test updating team attributes.
     *
     * @test
     */
    public function it_can_update_team_attributes()
    {
        $organization = Organization::create([
            'name' => 'Test Organization',
            'description' => 'Organization for testing',
        ]);

        $team = Team::create([
            'name' => 'Original Team Name',
            'description' => 'Original team description',
            'organization_id' => $organization->id,
        ]);

        $team->update([
            'name' => 'Updated Team Name',
            'description' => 'Updated team description',
        ]);

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'name' => 'Updated Team Name',
            'description' => 'Updated team description',
        ]);
    }

    /**
     * Test team requires organization.
     *
     * @test
     */
    public function it_requires_organization_id()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Team::create([
            'name' => 'Orphan Team',
            'description' => 'Team without organization',
        ]);
    }

    /**
     * Test finding team by name within organization.
     *
     * @test
     */
    public function it_can_find_team_by_name_in_organization()
    {
        $organization = Organization::create([
            'name' => 'Search Organization',
            'description' => 'Organization for search testing',
        ]);

        $team = Team::create([
            'name' => 'Unique Team Name',
            'description' => 'Searchable team',
            'organization_id' => $organization->id,
        ]);

        $found = Team::where('name', 'Unique Team Name')
                     ->where('organization_id', $organization->id)
                     ->first();

        $this->assertNotNull($found);
        $this->assertEquals($team->id, $found->id);
        $this->assertEquals('Unique Team Name', $found->name);
        $this->assertEquals($organization->id, $found->organization_id);
    }

    /**
     * Test team belongs to organization relationship.
     *
     * @test
     */
    public function it_belongs_to_organization()
    {
        $organization = Organization::create([
            'name' => 'Parent Organization',
            'description' => 'Organization that owns teams',
        ]);

        $team = Team::create([
            'name' => 'Child Team',
            'description' => 'Team that belongs to organization',
            'organization_id' => $organization->id,
        ]);

        $this->assertInstanceOf(Organization::class, $team->organization);
        $this->assertEquals($organization->id, $team->organization->id);
        $this->assertEquals('Parent Organization', $team->organization->name);
    }
}
