<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Role::firstOrCreate(
            ["name" => "admin"], 
            ["description" => "Administrateur avec accès complet"]
        );
        
        $user = Role::firstOrCreate(
            ["name" => "user"], 
            ["description" => "Utilisateur standard"]
        );
        
        $instructor = Role::firstOrCreate(
            ["name" => "instructor"], 
            ["description" => "Créateur de quiz et formateur"]
        );
        
        // Force ID assignment to ensure consistent IDs across environments
        if ($admin->id !== 1) {
            \DB::statement("UPDATE roles SET id = 1 WHERE name = 'admin'");
        }
        
        if ($user->id !== 2) {
            \DB::statement("UPDATE roles SET id = 2 WHERE name = 'user'");
        }
        
        if ($instructor->id !== 3) {
            \DB::statement("UPDATE roles SET id = 3 WHERE name = 'instructor'");
        }
        
        // Reset ID sequence to prevent conflicts
        $maxId = max(3, Role::max('id'));
        if (config('database.default') === 'mysql') {
            \DB::statement("ALTER TABLE roles AUTO_INCREMENT = " . ($maxId + 1));
        } elseif (config('database.default') === 'pgsql') {
            \DB::statement("SELECT setval('roles_id_seq', {$maxId})");
        } elseif (config('database.default') === 'sqlite') {
            // SQLite handles sequences differently, no action needed
        }
        
        // Log successful seeding for debugging in CI/CD environments
        \Log::info('Role seeder completed successfully - Admin ID: ' . $admin->id . ', User ID: ' . $user->id);
    }
}
