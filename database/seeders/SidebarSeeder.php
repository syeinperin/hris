<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sidebar;

class SidebarSeeder extends Seeder
{
    public function run(): void
    {
        Sidebar::truncate();

        // ── Top‐Level Items ────────────────────────────────

        // Dashboard: everyone
        Sidebar::create([
            'title'     => 'Dashboard',
            'route'     => 'dashboard',
            'icon'      => 'speedometer2',
            'parent_id' => null,
            'order'     => 1,
            'roles'     => ['hr','supervisor','employee'],
        ]);

        // Announcements: everyone
        Sidebar::create([
            'title'     => 'Announcements',
            'route'     => 'announcements.index',
            'icon'      => 'bullhorn',
            'parent_id' => null,
            'order'     => 2,
            'roles'     => ['hr','supervisor'],
        ]);

        // User List: HR only
        Sidebar::create([
            'title'     => 'User List',
            'route'     => 'users.index',
            'icon'      => 'address-book',
            'parent_id' => null,
            'order'     => 3,
            'roles'     => ['hr'],
        ]);

        // Approvals: HR & Supervisor
        Sidebar::create([
            'title'     => 'Approvals',
            'route'     => 'approvals.index',
            'icon'      => 'check-circle',
            'parent_id' => null,
            'order'     => 4,
            'roles'     => ['hr','supervisor'],
        ]);

        // Departments: HR & Supervisor
        Sidebar::create([
            'title'     => 'Departments',
            'route'     => 'departments.index',
            'icon'      => 'layers',
            'parent_id' => null,
            'order'     => 5,
            'roles'     => ['hr','supervisor'],
        ]);

        // Designations: HR & Supervisor
        Sidebar::create([
            'title'     => 'Designations',
            'route'     => 'designations.index',
            'icon'      => 'briefcase',
            'parent_id' => null,
            'order'     => 6,
            'roles'     => ['hr','supervisor'],
        ]);

        // Employee List: HR only
        Sidebar::create([
            'title'     => 'Employee List',
            'route'     => 'employees.index',
            'icon'      => 'person-lines-fill',
            'parent_id' => null,
            'order'     => 7,
            'roles'     => ['hr'],
        ]);

        // Attendance List: HR only
        Sidebar::create([
            'title'     => 'Attendance List',
            'route'     => 'attendance.index',
            'icon'      => 'clipboard-data',
            'parent_id' => null,
            'order'     => 8,
            'roles'     => ['hr'],
        ]);

        // Schedule: Supervisor only
        Sidebar::create([
            'title'     => 'Schedule',
            'route'     => 'schedule.index',
            'icon'      => 'calendar-check',
            'parent_id' => null,
            'order'     => 9,
            'roles'     => ['supervisor'],
        ]);

        // Payroll List: HR only
        Sidebar::create([
            'title'     => 'Payroll List',
            'route'     => 'payroll.index',
            'icon'      => 'file-earmark-text',
            'parent_id' => null,
            'order'     => 10,
            'roles'     => ['hr'],
        ]);

        // Salary Rates: HR only
        Sidebar::create([
            'title'     => 'Salary Rates',
            'route'     => 'designations.index',
            'icon'      => 'percent',
            'parent_id' => null,
            'order'     => 11,
            'roles'     => ['hr'],
        ]);

        // Deductions: HR only
        Sidebar::create([
            'title'     => 'Deductions',
            'route'     => 'deductions.index',
            'icon'      => 'credit-card-2-back',
            'parent_id' => null,
            'order'     => 12,
            'roles'     => ['hr'],
        ]);

        // ── Performance Evaluation ──────────────────────────
        $perfParent = Sidebar::create([
            'title'     => 'Performance Evaluation',
            'route'     => null,
            'icon'      => 'graph-up',
            'parent_id' => null,
            'order'     => 13,
            'roles'     => ['supervisor','employee'],
        ]);

        Sidebar::create([
            'title'     => 'Manage Forms',
            'route'     => 'performance.forms.index',
            'icon'      => null,
            'parent_id' => $perfParent->id,
            'order'     => 1,
            'roles'     => ['supervisor'],
        ]);

        Sidebar::create([
            'title'     => 'Fill Evaluations',
            'route'     => 'evaluations.index',
            'icon'      => null,
            'parent_id' => $perfParent->id,
            'order'     => 2,
            'roles'     => ['supervisor'],
        ]);

        Sidebar::create([
            'title'     => 'My Evaluations',
            'route'     => 'my.evaluations.index',
            'icon'      => null,
            'parent_id' => $perfParent->id,
            'order'     => 3,
            'roles'     => ['employee'],
        ]);

        // ── Global Reports ───────────────────────────────────
        Sidebar::create([
            'title'     => 'Reports',
            'route'     => 'reports.index',
            'icon'      => 'file-earmark-bar-graph',
            'parent_id' => null,
            'order'     => 14,
            'roles'     => ['hr','supervisor'],
        ]);

        // ── Employee-Specific ────────────────────────────────

        // My Dashboard
        Sidebar::create([
            'title'     => 'My Dashboard',
            'route'     => 'dashboard.employee',
            'icon'      => 'house',
            'parent_id' => null,
            'order'     => 15,
            'roles'     => ['employee'],
        ]);

        // My Leave Requests
        Sidebar::create([
            'title'     => 'My Leave Requests',
            'route'     => 'leaves.index',
            'icon'      => 'calendar',
            'parent_id' => null,
            'order'     => 16,
            'roles'     => ['employee'],
        ]);

        // Payslips
        Sidebar::create([
            'title'     => 'Payslips',
            'route'     => 'payslips.index',
            'icon'      => 'file-earmark-text',
            'parent_id' => null,
            'order'     => 17,
            'roles'     => ['employee'],
        ]);
    }
}
