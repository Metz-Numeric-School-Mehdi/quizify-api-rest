<?php

namespace Tests;

use App\Models\Role;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    /**
     * Setup the base test environment.
     *
     * Ensures that roles exist in the database for all tests.
     * This avoids integrity constraint violations when creating users.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Only try to create roles if the roles table exists
        // This prevents errors in simple tests that don't need the database
        try {
            if (Schema::hasTable('roles') && !app()->environment('production')) {
                // Force specific IDs for CI/CD environment compatibility
                $this->createRoleWithId('admin', 'Administrateur', 1);
                $this->createRoleWithId('user', 'Utilisateur standard', 2);
            }
        } catch (\Exception $e) {
            // Table might not exist in simple tests, so ignore errors
        }
    }
    
    /**
     * Create a role with a specific ID to ensure consistency across environments
     *
     * @param string $name The name of the role
     * @param string $description The description of the role
     * @param int $id The desired ID for the role
     * @return Role The created role
     */
    protected function createRoleWithId(string $name, string $description, int $id): Role
    {
        // First check if role already exists
        $role = Role::where('name', $name)->first();
        
        if (!$role) {
            // Create the role if it doesn't exist
            $role = Role::create([
                'name' => $name,
                'description' => $description
            ]);
            
            // Update the ID if needed (for MySQL environments)
            // Handle different database systems
            $dbType = config('database.default');
            if ($role->id != $id) {
                if ($dbType === 'mysql') {
                    DB::statement("UPDATE roles SET id = {$id} WHERE name = '{$name}'");
                    
                    // Reset auto increment if needed
                    $maxId = max($id, Role::max('id'));
                    DB::statement("ALTER TABLE roles AUTO_INCREMENT = " . ($maxId + 1));
                } elseif ($dbType === 'sqlite') {
                    // For SQLite, we need to update the ID differently
                    DB::update("UPDATE roles SET id = ? WHERE name = ?", [$id, $name]);
                }
                
                // Refresh the model
                $role = Role::find($id);
            }
        }
        
        return $role;
    }
}
