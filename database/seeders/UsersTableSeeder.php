<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Disable FKs, truncate users, then re-enable FKs
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2) Seed an HR user
        $hrRole = Role::where('name', 'hr')->firstOrFail();
        User::create([
            'name'     => 'HR Admin',
            'email'    => 'hr@example.com',
            'password' => Hash::make('password'),  // change this!
            'role_id'  => $hrRole->id,
            'status'   => 'active',
        ]);

        // 3) Seed a Supervisor user
        $supRole = Role::where('name', 'supervisor')->firstOrFail();
        User::create([
            'name'     => 'Supervisor User',
            'email'    => 'supervisor@example.com',
            'password' => Hash::make('password'),
            'role_id'  => $supRole->id,
            'status'   => 'active',
        ]);

        // 4) Seed a Sample Employee
        $empRole = Role::where('name', 'employee')->firstOrFail();
        User::create([
            'name'     => 'Sample Employee',
            'email'    => 'employee@example.com',
            'password' => Hash::make('password'),
            'role_id'  => $empRole->id,
            'status'   => 'active',
        ]);
    }
}
