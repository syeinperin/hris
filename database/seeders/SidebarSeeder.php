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

        Sidebar::create([
            'name' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => 'dashboard-icon',
            'parent_id' => null,
            'order' => 1,
            'role' => json_encode(['admin', 'supervisor']),
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

        Sidebar::create([
            'name' => 'Employees',
            'route' => 'employees.index',
            'icon' => 'employee-icon',
            'parent_id' => null,
            'order' => 3,
            'role' => json_encode(['admin', 'accounting']),
        ]);
    }
    }


