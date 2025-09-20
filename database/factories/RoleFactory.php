<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 * Factory for creating role instances with proper CI/CD compatibility
 */
class RoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $index = 0;
        // Use predictable names to avoid test flakiness
        $roleNames = ['viewer', 'contributor', 'editor', 'manager', 'tester'];
        $name = $roleNames[$index % count($roleNames)] . '_' . ($index + 1);
        $index++;
        
        return [
            'name' => $name,
            'description' => 'Rôle ' . $name,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the role is an admin.
     *
     * @return static
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'admin',
            'description' => 'Administrateur avec accès complet au système',
        ]);
    }

    /**
     * Indicate that the role is a regular user.
     *
     * @return static
     */
    public function user(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'user',
            'description' => 'Utilisateur standard avec accès limité',
        ]);
    }
}