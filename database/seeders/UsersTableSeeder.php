<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate the users table
        User::truncate();
        
        // Enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Get role ids from the roles table
        $adminRole = Role::where('name', 'admin')->first();
        $hrRole = Role::where('name', 'hr')->first();
        $employeeRole = Role::where('name', 'employee')->first();
        $supervisorRole = Role::where('name', 'supervisor')->first();
        $timekeeperRole = Role::where('name', 'timekeeper')->first();

        // Check if roles exist, and log an error if not
        if (!$adminRole || !$hrRole || !$employeeRole || !$supervisorRole || !$timekeeperRole) {
            throw new \Exception('One or more roles are missing in the database.');
        }

        // Seeding users
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id, // Use role_id instead of role
        ]);
    }
}
