<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks, truncate, then re‑enable
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Fetch the admin role (throws if missing)
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        // Create the one-and-only Admin user
        User::create([
            'name'              => 'Admin User',
            'email'             => 'admin@example.com',
            'password'          => Hash::make('password'),        // change this!
            'role_id'           => $adminRole->id,
            'status'            => 'active',                     // always active
        ]);
    }
}
