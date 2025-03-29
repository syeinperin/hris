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
        // User Management
        Sidebar::create([
            'name' => 'User Account Management',
            'route' => 'users.index',  
            'icon' => 'users',
            'parent_id' => null,
            'order' => 4,
            'role' => json_encode(['admin']),  
        ]);        
        // Organization
        $organization = Sidebar::create([
            'name' => 'Organization',
            'route' => null, 
            'icon' => 'building', 
            'parent_id' => null,
            'order' => 1,
            'role' => json_encode(['admin', 'hr']),
        ]);
        // Department
        Sidebar::create([
            'name' => 'Departments',
            'route' => 'departments.index',
            'icon' => 'layers', 
            'parent_id' => $organization->id, 
            'order' => 2,
            'role' => json_encode(['admin', 'hr']),
        ]);

        Sidebar::create([
            'name' => 'Designations',
            'route' => 'designations.index',
            'icon' => 'briefcase',
            'parent_id' => $organization->id,
            'order' => 3,
            'role' => json_encode(['admin', 'hr']),
        ]);
        
        // Employees
        $employees = Sidebar::create([
            'name' => 'Employee Management',
            'route' => null, 
            'icon' => 'users', 
            'parent_id' => null,
            'order' => 2,
            'role' => json_encode(['admin', 'hr']),
        ]);

        Sidebar::create([
            'name' => 'Employee Management',
            'route' => 'employees.index',
            'icon' => 'user-check',
            'parent_id' => $employees->id,
            'order' => 1,
            'role' => json_encode(['admin', 'hr']),
        ]);

        Sidebar::create([
            'name' => 'Attendance',
            'route' => 'attendance.index',
            'icon' => 'attendance-icon',
            'parent_id' => null,
            'order' => 2,
            'role' => json_encode(['admin', 'hr']),
        ]);

        Sidebar::create([
            'name' => 'Reports',
            'route' => 'reports.index',
            'icon' => 'report-icon',
            'parent_id' => null,
            'order' => 3,
            'role' => json_encode(['admin', 'accounting']),
        ]);
    }
}
