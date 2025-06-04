<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesTableSeeder extends Seeder
{
    public function run()
    {
        // 1) Define only the three roles we want
        $roles = ['hr', 'supervisor', 'employee'];

        // 2) (Optional) Define your permissions
        $perms = [
            'view dashboard',
            'manage employees',
            'manage attendance',
            'manage payroll',
            'manage leave',
            'manage performance',
            // add any other permissions you need...
        ];

        // 3) Create Permissions
        foreach ($perms as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // 4) Create Roles
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // 5) Assign permissions to roles
        // HR gets broad operational permissions
        $hr = Role::findByName('hr');
        $hr->syncPermissions([
            'view dashboard',
            'manage employees',
            'manage attendance',
            'manage payroll',
            'manage leave',
        ]);

        // Supervisor handles scheduling, training & performance
        $sup = Role::findByName('supervisor');
        $sup->syncPermissions([
            'view dashboard',
            'manage attendance',
            'manage performance',
        ]);

        // Employee only sees their own dashboard
        $emp = Role::findByName('employee');
        $emp->syncPermissions([
            'view dashboard',
        ]);
    }
}
