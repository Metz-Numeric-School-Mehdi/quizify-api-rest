<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Test user profile update functionality
 */
class UserProfileUpdateTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('minio');
    }

    /**
     * Test successful profile update with basic information
     */
    public function test_user_can_update_basic_profile_information()
    {
        $user = User::factory()->create([
            'username' => 'oldusername',
            'email' => 'old@example.com',
            'firstname' => 'OldFirst',
            'lastname' => 'OldLast'
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/user/profile', [
            'username' => 'newusername',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john.doe@example.com'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Profil mis à jour avec succès.',
                ])
                ->assertJsonStructure([
                    'message',
                    'user' => [
                        'id',
                        'username',
                        'firstname',
                        'lastname',
                        'email'
                    ]
                ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'username' => 'newusername',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john.doe@example.com'
        ]);
    }

    /**
     * Test password update
     */
    public function test_user_can_update_password()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/user/profile', [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Profil mis à jour avec succès.',
                ]);

        // Verify password was updated
        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));
    }

    /**
     * Test validation errors
     */
    public function test_profile_update_validation_errors()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create(['email' => 'taken@example.com']);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/user/profile', [
            'email' => 'taken@example.com', // Already taken
            'password' => 'weak' // Too weak
        ]);

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'message',
                    'errors'
                ]);
    }

    /**
     * Test unauthenticated access
     */
    public function test_unauthenticated_user_cannot_update_profile()
    {
        $response = $this->putJson('/api/user/profile', [
            'username' => 'newusername'
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test profile photo upload
     */
    public function test_user_can_upload_profile_photo()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->image('profile.jpg');

        $response = $this->putJson('/api/user/profile', [
            'username' => 'testuser',
            'profile_photo' => $file
        ]);

        $response->assertStatus(200);

        // Verify file was stored
        $updatedUser = $user->fresh();
        $this->assertNotNull($updatedUser->profile_photo);
        $this->assertTrue(Storage::disk('minio')->exists($updatedUser->profile_photo));
    }
}
