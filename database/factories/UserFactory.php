<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Ensure roles exist in database before creating users
        $this->ensureRolesExist();
        
        return [
            "username" => fake()->userName(),
            "lastname" => fake()->lastName(),
            "firstname" => fake()->firstName(),
            "role_id" => function () {
                // Handle case where roles table might not exist yet
                try {
                    if (Schema::hasTable('roles')) {
                        $role = Role::where('name', 'user')->first();
                        if ($role) {
                            return $role->id;
                        }
                    }
                    return 2; // Default user role ID
                } catch (\Exception $e) {
                    return 2; // Default user role ID if any error occurs
                }
            },
            "email" => fake()->email(),
            "password" => (static::$password ??= Hash::make("password")),
        ];
    }
    
    /**
     * Ensure that required roles exist in the database.
     * This is critical for CI/CD environments where order of operations may vary.
     */
    protected function ensureRolesExist(): void
    {
        try {
            // Only attempt to create roles if the table exists
            if (Schema::hasTable('roles') && Role::count() === 0) {
                // Create basic roles if they don't exist
                Role::create(['name' => 'admin', 'description' => 'Administrateur']);
                Role::create(['name' => 'user', 'description' => 'Utilisateur standard']);
            }
        } catch (\Exception $e) {
            // If table doesn't exist or other error, just continue
            // This handles in-memory SQLite tests where migrations may run later
        }
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "email_verified_at" => null,
            ]
        );
    }
    
    /**
     * Configure the model factory to create a user with admin role.
     */
    public function admin(): static
    {
        return $this->state(function () {
            $adminRole = Role::firstOrCreate(
                ['name' => 'admin'],
                ['description' => 'Administrator role']
            );
            
            return [
                'role_id' => $adminRole->id,
            ];
        });
    }
    
    /**
     * Configure the model factory to create a user with specific role.
     */
    public function withRole(string $roleName): static
    {
        return $this->state(function () use ($roleName) {
            try {
                if (Schema::hasTable('roles')) {
                    $role = Role::firstOrCreate(
                        ['name' => $roleName],
                        ['description' => ucfirst($roleName) . ' role']
                    );
                    
                    return [
                        'role_id' => $role->id,
                    ];
                }
            } catch (\Exception $e) {
                // Fallback to default IDs if table doesn't exist
            }
            
            // Default IDs based on common role names
            $roleIds = [
                'admin' => 1,
                'user' => 2,
                'instructor' => 3
            ];
            
            return [
                'role_id' => $roleIds[$roleName] ?? 2, // Default to user role
            ];
        });
    }
}
