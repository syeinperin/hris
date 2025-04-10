<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sidebar;

class SidebarSeeder extends Seeder
{
    public function run(): void
    {
        // Clear out the sidebar table
        Sidebar::truncate(); 

        // Dashboard
        Sidebar::create([
            'name' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => 'gauge',
            'parent_id' => null,
            'order' => 1,
            'role' => json_encode(['admin', 'supervisor']),
        ]);

        // 1. User Account Management (Parent)
        $userMgmt = Sidebar::create([
            'name' => 'User Account Management',
            'route' => null,  // Parent item has no route
            'icon' => 'users',
            'parent_id' => null,
            'order' => 2,
            'role' => json_encode(['admin']),
        ]);

        Sidebar::create([
            'name' => 'User Account List',
            'route' => 'users.index',
            'icon' => 'address-book',
            'parent_id' => $userMgmt->id,
            'order' => 1,
            'role' => json_encode(['admin']),
        ]);

        Sidebar::create([
            'name' => 'User Approval',
            'route' => 'users.pending',
            'icon' => 'check-circle',
            'parent_id' => $userMgmt->id,
            'order' => 2,
            'role' => json_encode(['admin']),
        ]);

        // 2. Organization (Parent)
        $organization = Sidebar::create([
            'name' => 'Organization',
            'route' => null,
            'icon' => 'building',
            'parent_id' => null,
            'order' => 3,
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

        // 3. Employee Management (Parent)
        $employeeMgmt = Sidebar::create([
            'name' => 'Employee Management',
            'route' => null,
            'icon' => 'users-three',
            'parent_id' => null,
            'order' => 4,
            'role' => json_encode(['admin', 'hr']),
        ]);

        Sidebar::create([
            'name' => 'Employee List',
            'route' => 'employees.index',
            'icon' => 'address-book',
            'parent_id' => $employeeMgmt->id,
            'order' => 1,
            'role' => json_encode(['admin', 'hr']),
        ]);

        // 4. Attendance (Parent)
        $attendance = Sidebar::create([
            'name' => 'Attendance',
            'route' => null,
            'icon' => 'clock',
            'parent_id' => null,
            'order' => 5,
            'role' => json_encode(['admin', 'hr']),
        ]);

        Sidebar::create([
            'name' => 'Kiosk Attendance',
            'route' => 'attendance.log.form',
            'icon' => 'clock',
            'parent_id' => $attendance->id,
            'order' => 1,
            'role' => json_encode(['admin', 'hr']),
        ]);

        Sidebar::create([
            'name' => 'Attendance List',
            'route' => 'attendance.index',
            'icon' => 'clipboard-text',
            'parent_id' => $attendance->id,
            'order' => 2,
            'role' => json_encode(['admin', 'hr']),
        ]);

        Sidebar::create([
            'name' => 'Schedule',
            'route' => 'schedule.index',
            'icon' => 'calendar',
            'parent_id' => $attendance->id,
            'order' => 3,
            'role' => json_encode(['admin', 'hr']),
        ]);

        // 5. Payroll (Parent)
        $payroll = Sidebar::create([
            'name' => 'Payroll',
            'route' => null,
            'icon' => 'dollar-sign',
            'parent_id' => null,
            'order' => 6,
            'role' => json_encode(['admin', 'hr']),
        ]);

        Sidebar::create([
            'name' => 'Payroll List',
            'route' => 'payroll.index',
            'icon' => 'briefcase',
            'parent_id' => $payroll->id,
            'order' => 1,
            'role' => json_encode(['admin', 'hr']),
        ]);

        Sidebar::create([
            'name' => 'Salary Rate',
            'route' => 'designations.index',
            'icon' => 'briefcase',
            'parent_id' => $payroll->id,
            'order' => 2,
            'role' => json_encode(['admin', 'hr']),
        ]);

        // 6. Reports
        Sidebar::create([
            'name' => 'Reports',
            'route' => 'reports.index',
            'icon' => 'file-text',
            'parent_id' => null,
            'order' => 7,
            'role' => json_encode(['admin', 'accounting']),
        ]);
    }
}
