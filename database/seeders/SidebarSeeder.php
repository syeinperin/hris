<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sidebar;

class SidebarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Sidebar::truncate(); 

        // Dashboard
        Sidebar::create([
            'name' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => 'dashboard-icon',
            'parent_id' => null,
            'order' => 1,
            'role' => json_encode(['admin', 'supervisor']),
        ]);
        // 1. User Account Management
        Sidebar::create([
            'name' => 'User Account Management',
            'route' => 'users.index',
            'icon' => 'users',
            'parent_id' => null,
            'order' => 1,
            'role' => json_encode(['admin']),
        ]);

        // 2. Organization (with sub-items)
        $organization = Sidebar::create([
            'name' => 'Organization',
            'route' => null,
            'icon' => 'building',
            'parent_id' => null,
            'order' => 2,
            'role' => json_encode(['admin', 'hr']),
        ]);

        Sidebar::create([
            'name' => 'Departments',
            'route' => 'departments.index',
            'icon' => 'layers',
            'parent_id' => $organization->id,
            'order' => 1,
            'role' => json_encode(['admin', 'hr']),
        ]);

        Sidebar::create([
            'name' => 'Designations',
            'route' => 'designations.index',
            'icon' => 'briefcase',
            'parent_id' => $organization->id,
            'order' => 2,
            'role' => json_encode(['admin', 'hr']),
        ]);

        // 3. Employee Management (with sub-item)
        $employees = Sidebar::create([
            'name' => 'Employee Management',
            'route' => null,
            'icon' => 'users',
            'parent_id' => null,
            'order' => 3,
            'role' => json_encode(['admin', 'hr']),
        ]);

        Sidebar::create([
            'name' => 'Add Employee',
            'route' => 'employees.create',
            'icon' => 'user-plus',
            'parent_id' => $employees->id,
            'order' => 1,
            'role' => json_encode(['admin', 'hr']),
        ]);

        // 4. Attendance
        Sidebar::create([
            'name' => 'Attendance',
            'route' => 'attendance.index',
            'icon' => 'clock',
            'parent_id' => null,
            'order' => 4,
            'role' => json_encode(['admin', 'hr']),
        ]);

        
        // 6. Reports
        Sidebar::create([
            'name' => 'Reports',
            'route' => 'reports.index',
            'icon' => 'file-text',
            'parent_id' => null,
            'order' => 6,
            'role' => json_encode(['admin', 'accounting']),
        ]);

    }
}
