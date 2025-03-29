<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RolesTableSeeder extends Seeder
{
    public function run()
    {
        // Define the roles
        $roles = ['admin', 'hr', 'employee', 'supervisor', 'timekeeper'];

        // Insert roles into the roles table, only if they do not already exist
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }
}


