<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);
        
        User::create([
            'name' => 'HR Manager',
            'email' => 'hr@example.com',
            'password' => bcrypt('password'),
            'role' => 'hr'
        ]);
        
        User::create([
            'name' => 'Employee',
            'email' => 'employee@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee'
        ]);

        User::create([
            'name' => 'Supervisor',
            'email' => 'supervisor@example.com',
            'password' => bcrypt('password'),
            'role' => 'supervisor'
        ]);

        User::create([
            'name' => 'Timekeeper',
            'email' => 'timekeeper@example.com',
            'password' => bcrypt('password'),
            'role' => 'timekeeper'
        ]);
    }
}
