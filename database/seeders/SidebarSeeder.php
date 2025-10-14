<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sidebar;

class SidebarSeeder extends Seeder
{
    public function run(): void
    {
        // Reset
        Sidebar::truncate();

        // ── Top-Level (HR/Supervisor) ─────────────────────
        Sidebar::create([
            'title'     => 'Dashboard',
            'route'     => 'dashboard',
            'icon'      => 'speedometer2',
            'parent_id' => null,
            'order'     => 1,
            'roles'     => ['hr','supervisor'],
        ]);

        Sidebar::create([
            'title'     => 'Announcements',
            'route'     => 'announcements.index',
            'icon'      => 'bullhorn',
            'parent_id' => null,
            'order'     => 2,
            'roles'     => ['hr','supervisor'],
        ]);

        Sidebar::create([
            'title'     => 'User List',
            'route'     => 'users.index',
            'icon'      => 'address-book',
            'parent_id' => null,
            'order'     => 3,
            'roles'     => ['hr'],
        ]);

        Sidebar::create([
            'title'     => 'Approvals',
            'route'     => 'approvals.index',
            'icon'      => 'check-circle',
            'parent_id' => null,
            'order'     => 4,
            'roles'     => ['hr','supervisor'],
        ]);

        // HR/Supervisor evaluation manager
        Sidebar::create([
            'title'     => 'Performance Evaluation',
            'route'     => 'evaluations.index',
            'icon'      => 'clipboard2-check',
            'parent_id' => null,
            'order'     => 5,
            'roles'     => ['hr','supervisor'],
        ]);

        Sidebar::create([
            'title'     => 'Employee List',
            'route'     => 'employees.index',
            'icon'      => 'person-lines-fill',
            'parent_id' => null,
            'order'     => 6,
            'roles'     => ['hr', 'supervisor'],
        ]);

        Sidebar::create([
            'title'     => 'Attendance List',
            'route'     => 'attendance.index',
            'icon'      => 'clipboard-data',
            'parent_id' => null,
            'order'     => 7,
            'roles'     => ['hr'],
        ]);

        Sidebar::create([
            'title'     => 'Schedule',
            'route'     => 'schedule.index',
            'icon'      => 'calendar-check',
            'parent_id' => null,
            'order'     => 8,
            'roles'     => ['supervisor'],
        ]);

        Sidebar::create([
            'title'     => 'Payroll List',
            'route'     => 'payroll.index',
            'icon'      => 'file-earmark-text',
            'parent_id' => null,
            'order'     => 9,
            'roles'     => ['hr'],
        ]);

        // ── Global Reports ────────────────────────────────
        Sidebar::create([
            'title'     => 'Reports',
            'route'     => 'reports.index',
            'icon'      => 'file-earmark-bar-graph',
            'parent_id' => null,
            'order'     => 11,
            'roles'     => ['hr','supervisor'],
        ]);

        // ── Employee-only (Self-Service) ──────────────────
        Sidebar::create([
            'title'     => 'My Dashboard',
            'route'     => 'dashboard.employee',
            'icon'      => 'house',
            'parent_id' => null,
            'order'     => 12,
            'roles'     => ['employee'],
        ]);

        Sidebar::create([
            'title'     => 'My Leave Requests',
            'route'     => 'leaves.index',
            'icon'      => 'calendar',
            'parent_id' => null,
            'order'     => 13,
            'roles'     => ['employee'],
        ]);

        Sidebar::create([
            'title'     => 'Payslips',
            'route'     => 'payslips.index',
            'icon'      => 'file-earmark-text',
            'parent_id' => null,
            'order'     => 14,
            'roles'     => ['employee'],
        ]);

        Sidebar::create([
            'title'     => 'My Loans',
            // ✅ correct route name for employee loans
            'route'     => 'employee.loans.index',
            'icon'      => 'piggy-bank',
            'parent_id' => null,
            'order'     => 15,
            'roles'     => ['employee'],
        ]);

        // Employee evaluation self-view
        Sidebar::create([
            'title'     => 'My Evaluations',
            'route'     => 'my.evaluations.index',
            'icon'      => 'clipboard2-check',
            'parent_id' => null,
            'order'     => 16,
            'roles'     => ['employee'],
        ]);

        // ✅ NEW: My Documents (employee self-service)
        Sidebar::create([
            'title'     => 'My Documents',
            'route'     => 'mydocs.index',
            'icon'      => 'files',
            'parent_id' => null,
            'order'     => 17,
            'roles'     => ['employee'],
        ]);

        // ── Face Recognition (ONE combined menu item) ─────
        Sidebar::create([
            'title'     => 'Face Recognition',
            'route'     => 'face.index',
            'icon'      => 'camera-video',
            'parent_id' => null,
            'order'     => 19,
            'roles'     => ['hr','supervisor'],
        ]);

        Sidebar::create([
    'title'     => 'Offboarding',
    'route'     => 'offboarding.index',
    'icon'      => 'box-arrow-right', // Bootstrap icon key
    'parent_id' => null,
    'order'     => 10,                 // adjust position as you like
    'roles'     => ['hr','supervisor'],
]);

Sidebar::create([
    'title'     => 'My Time Card',
    'route'     => 'timecard.index',   // ← was 'timecard.index'
    'icon'      => 'journal-check',
    'parent_id' => null,
    'order'     => 18,
    'roles'     => ['employee'],
]);

    }
}
