<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Team;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Badge;
use App\Models\Quiz;
use Database\Seeders\RoleSeeder;
use Database\Seeders\BadgeSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\QuizLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

/**
 * User model unit tests.
 * 
 * This test class verifies the core functionality of the User model,
 * including creation, relationships, authentication, and profile management.
 */
class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     * 
     * Seeds the database with necessary data for testing user functionality,
     * including roles, badges, and other required dependencies.
     * 
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            RoleSeeder::class,
            BadgeSeeder::class,
            CategorySeeder::class,
            QuizLevelSeeder::class,
        ]);
    }

    /**
     * Test creating a user with basic attributes and role relationship.
     * 
     * Verifies that a user can be created and properly associated with a role.
     *
     * @test
     * @return void
     */
    public function it_creates_a_user()
    {
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
    
    /**
     * Test the password hashing functionality.
     * 
     * Verifies that user passwords are properly hashed when set.
     *
     * @test
     * @return void
     */
    public function it_hashes_user_password()
    {
        $plainPassword = 'secret_password';
        $role = Role::where("name", "user")->first();
        
        $user = User::factory()->create([
            'password' => $plainPassword,
            'role_id' => $role->id
        ]);
        
        // Check that the password is hashed
        $this->assertNotEquals($plainPassword, $user->password);
        
        // If you have access to Hash::check, you could also verify the hash works
        $this->assertTrue(Hash::check($plainPassword, $user->password));
    }
    
    /**
     * Test user relationship with badges.
     * 
     * Verifies that badges can be assigned to users through the many-to-many relationship.
     *
     * @test
     * @return void
     */
    public function it_can_associate_badges_with_user()
    {
        $user = User::factory()->create();
        // Utilisons les badges créés par le seeder (il n'y en a que 2)
        $badges = Badge::all();
        
        foreach ($badges as $badge) {
            $user->badges()->attach($badge->id);
        }
        
        // Rechargeons la relation pour être sûr d'avoir les données à jour
        $user->load('badges');
        
        $this->assertCount($badges->count(), $user->badges);
        
        foreach ($badges as $badge) {
            $this->assertTrue($user->badges->contains($badge->id));
        }
    }
    
    /**
     * Test user relationship with created quizzes.
     * 
     * Verifies that users can create quizzes and access them through the relationship.
     *
     * @test
     * @return void
     */
    public function it_can_create_quizzes()
    {
        $user = User::factory()->create();
        $category = \App\Models\Category::first();
        $level = \App\Models\QuizLevel::first();
        
        // Create 3 quizzes for the user
        for ($i = 0; $i < 3; $i++) {
            Quiz::create([
                'title' => "User Quiz {$i}",
                'slug' => "user-quiz-{$i}",
                'description' => "Quiz created by user for testing",
                'is_public' => true,
                'level_id' => $level->id,
                'status' => 'published',
                'user_id' => $user->id,
                'duration' => 30,
                'pass_score' => 70,
                'category_id' => $category->id,
            ]);
        }
        
        $this->assertCount(3, $user->quizzesCreated);
    }
    
    /**
     * Test user relationship with organizations and teams.
     * 
     * Verifies that users can be associated with organizations and teams.
     *
     * @test
     * @return void
     */
    public function it_can_belong_to_organization_and_team()
    {
        $organization = new Organization();
        $organization->name = 'Test Organization';
        $organization->save();
        
        $team = new Team();
        $team->name = 'Test Team';
        $team->organization_id = $organization->id;
        $team->save();
        
        $user = User::factory()->create([
            'organization_id' => $organization->id,
            'team_id' => $team->id
        ]);
        
        $this->assertInstanceOf(Organization::class, $user->organization);
        $this->assertEquals('Test Organization', $user->organization->name);
        
        $this->assertInstanceOf(Team::class, $user->team);
        $this->assertEquals('Test Team', $user->team->name);
    }
    
    /**
     * Test soft deleting users.
     * 
     * Verifies that when a user is deleted, they are soft deleted instead of
     * being completely removed from the database.
     *
     * @test
     * @return void
     */
    public function it_can_soft_delete_user()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'username' => 'testuser'
        ]);
        
        $userId = $user->id;
        $user->delete();
        
        // User should be soft deleted
        $this->assertSoftDeleted('users', ['id' => $userId]);
        
        // Should not be able to create a new user with the same unique fields
        $newUser = User::factory()->make([
            'email' => 'test@example.com',
            'username' => 'testuser'
        ]);
        
        // Expect exception when trying to save a user with the same unique fields
        $this->expectException(\Illuminate\Database\QueryException::class);
        $newUser->save();
    }
}
