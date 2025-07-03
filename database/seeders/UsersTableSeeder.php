<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;                      // ← your domain roles table
use Spatie\Permission\Models\Role as SpatieRole; // ← Spatie’s roles

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Truncate users
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2) Ensure both domain & Spatie roles exist
        $roles = ['hr', 'supervisor', 'employee'];
        foreach ($roles as $name) {
            // a) seed your own roles table
            $domain = Role::firstOrCreate(['name' => $name]);

            // b) seed Spatie’s permission_roles table
            SpatieRole::firstOrCreate([
                'name'       => $name,
                'guard_name' => config('auth.defaults.guard'),
            ]);
        }

        // 3) Create an HR user
        $hrDomain = Role::where('name', 'hr')->first();
        $hr = User::create([
            'name'     => 'HR Admin',
            'email'    => 'hr@example.com',
            'password' => Hash::make('password'), // please change!
            'role_id'  => $hrDomain->id,
            'status'   => 'active',
        ]);
        $hr->assignRole('hr');

        // 4) Create a Supervisor user
        $supDomain = Role::where('name', 'supervisor')->first();
        $sup = User::create([
            'name'     => 'Supervisor User',
            'email'    => 'supervisor@example.com',
            'password' => Hash::make('password'),
            'role_id'  => $supDomain->id,
            'status'   => 'active',
        ]);
        $sup->assignRole('supervisor');

        // 5) Create a Sample Employee
        $empDomain = Role::where('name', 'employee')->first();
        $emp = User::create([
            'name'     => 'Sample Employee',
            'email'    => 'employee@example.com',
            'password' => Hash::make('password'),
            'role_id'  => $empDomain->id,
            'status'   => 'active',
        ]);
        $emp->assignRole('employee');
    }
}
