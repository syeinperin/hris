<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesTableSeeder extends Seeder
{
    public function run()
    {
        // 1) Define your roles
        $roles = ['admin','hr','employee','supervisor','timekeeper'];

        // 2) Define your permissions
        $perms = [
            'view dashboard',
            'export employees',
            'export attendance',
            'export payroll',
            'export payslips',
            'export performance',
            // add any other granular permissions you want
        ];

        // 3) Create Permissions
        foreach ($perms as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // 4) Create Roles
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // 5) Assign permissions to roles
        // Admin gets everything
        $admin = Role::findByName('admin');
        $admin->syncPermissions(Permission::all());

        // Give employees only dashboard access
        $employee = Role::findByName('employee');
        $employee->syncPermissions(['view dashboard']);

        // (Repeat for other roles as needed)
    }
}
