<?php

namespace Tests\Unit;

use App\Models\Badge;
use App\Models\User;
use App\Models\UserBadge;
use Database\Seeders\BadgeSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Badge model unit tests.
 * 
 * This test class verifies the core functionality of the Badge model,
 * including creation, relationships, updates, and soft deletion.
 */
class BadgeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     * 
     * Seeds the database with necessary data for testing badge functionality.
     * 
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            BadgeSeeder::class,
            RoleSeeder::class,
        ]);
    }

    /**
     * Test the creation of a badge.
     * 
     * Verifies that a badge can be created with proper attributes.
     *
     * @test
     * @return void
     */
    public function it_creates_a_badge()
    {
        $badge = Badge::create([
            "name" => "Expert",
            "description" => "Awarded for completing 20 quizzes with perfect scores.",
            "icon" => "expert.png",
        ]);

        $this->assertDatabaseHas("badges", [
            "id" => $badge->id,
            "name" => "Expert",
            "description" => "Awarded for completing 20 quizzes with perfect scores.",
            "icon" => "expert.png",
        ]);

        $this->assertInstanceOf(Badge::class, $badge);
    }

    /**
     * Test soft deleting a badge.
     * 
     * Verifies that badges can be soft deleted rather than permanently removed.
     *
     * @test
     * @return void
     */
    public function it_can_soft_delete_badge()
    {
        $badge = Badge::create([
            "name" => "Champion",
            "description" => "Awarded for winning a competition.",
            "icon" => "champion.png",
        ]);
        
        $badgeId = $badge->id;
        $badge->delete();
        
        $this->assertSoftDeleted('badges', ['id' => $badgeId]);
        
        // The record should still exist in the database
        $this->assertDatabaseHas('badges', [
            'id' => $badgeId,
            'deleted_at' => now(),
        ]);
    }

    /**
     * Test updating a badge's attributes.
     * 
     * Verifies that a badge's attributes can be updated and changes are saved.
     *
     * @test
     * @return void
     */
    public function it_can_update_badge_attributes()
    {
        $badge = Badge::create([
            "name" => "Novice",
            "description" => "Initial description",
            "icon" => "novice.png",
        ]);
        
        $badge->update([
            "name" => "Advanced Novice",
            "description" => "Updated description",
        ]);
        
        $this->assertDatabaseHas("badges", [
            "id" => $badge->id,
            "name" => "Advanced Novice",
            "description" => "Updated description",
            "icon" => "novice.png", // Unchanged attribute
        ]);
        
        $refreshedBadge = $badge->fresh();
        $this->assertEquals("Advanced Novice", $refreshedBadge->name);
        $this->assertEquals("Updated description", $refreshedBadge->description);
    }

    /**
     * Test the relationship between badges and users.
     * 
     * Verifies that badges can be assigned to users through the UserBadge model.
     *
     * @test
     * @return void
     */
    public function it_can_be_assigned_to_users()
    {
        // Create a user and get existing badges
        $user = User::factory()->create();
        $badges = Badge::take(2)->get();
        
        // Assign badges to user
        foreach ($badges as $badge) {
            UserBadge::create([
                'user_id' => $user->id,
                'badge_id' => $badge->id,
            ]);
        }
        
        // Get users with this badge
        $firstBadge = $badges->first();
        $this->assertTrue($user->badges->contains($firstBadge->id));
        
        // Check if the badge has been properly associated with the user
        $this->assertCount(2, $user->badges);
    }

    /**
     * Test retrieving the seeded badges.
     * 
     * Verifies that the badge seeder correctly populates the database.
     *
     * @test
     * @return void
     */
    public function it_retrieves_seeded_badges()
    {
        $badges = Badge::all();
        
        $this->assertGreaterThanOrEqual(2, $badges->count());
        
        // Verify that the seeded badges exist
        $this->assertDatabaseHas('badges', [
            'name' => 'Débutant',
        ]);
        
        $this->assertDatabaseHas('badges', [
            'name' => 'Assidu',
        ]);
    }
}