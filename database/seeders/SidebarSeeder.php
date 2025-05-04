<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sidebar;

class SidebarSeeder extends Seeder
{
    public function run(): void
    {
        Sidebar::truncate();

        // 1) Admin Dashboard
        Sidebar::create([
            'title' => 'Dashboard',
            'route' => 'dashboard',
            'icon'  => 'speedometer2',
            'order' => 1,
            'roles' => ['admin','hr','timekeeper','supervisor'],
        ]);
       
        $ua = Sidebar::create([
            'title' => 'User Accounts',
            'icon'  => 'users',
            'order' => 2,
            'roles' => ['admin'],
        ]);
        Sidebar::create([
            'title'     => 'User List',
            'route'     => 'users.index',
            'icon'      => 'address-book',
            'parent_id' => $ua->id,
            'order'     => 1,
            'roles'     => ['admin'],
        ]);
        Sidebar::create([
            'title'     => 'Approvals',
            'route'     => 'approvals.index',
            'icon'      => 'check-circle',
            'parent_id' => $ua->id,
            'order'     => 2,
            'roles'     => ['admin'],
        ]);

        //
        // 3) Organization — admin only
        //
        $org = Sidebar::create([
            'title' => 'Organization',
            'icon'  => 'building',
            'order' => 3,
            'roles' => ['admin'],
        ]);
        Sidebar::create([
            'title'     => 'Departments',
            'route'     => 'departments.index',
            'icon'      => 'layers',
            'parent_id' => $org->id,
            'order'     => 1,
            'roles'     => ['admin'],
        ]);
        Sidebar::create([
            'title'     => 'Designations',
            'route'     => 'designations.index',
            'icon'      => 'briefcase',
            'parent_id' => $org->id,
            'order'     => 2,
            'roles'     => ['admin'],
        ]);

        //
        // 4) Employees — hr + admin
        //
        $em = Sidebar::create([
            'title' => 'Employees',
            'icon'  => 'people-fill',
            'order' => 4,
            'roles' => ['hr','admin'],
        ]);
        Sidebar::create([
            'title'     => 'Employee List',
            'route'     => 'employees.index',
            'icon'      => 'person-lines-fill',
            'parent_id' => $em->id,
            'order'     => 1,
            'roles'     => ['hr','admin'],
        ]);

        //
        // 5) Attendance — timekeeper, supervisor, admin
        //
        $att = Sidebar::create([
            'title' => 'Attendance',
            'icon'  => 'clock',
            'order' => 5,
            'roles' => ['timekeeper','supervisor','admin'],
        ]);
        Sidebar::create([
            'title'     => 'Attendance List',
            'route'     => 'attendance.index',
            'icon'      => 'clipboard-data',
            'parent_id' => $att->id,
            'order'     => 1,
            'roles'     => ['timekeeper','supervisor','admin'],
        ]);
        Sidebar::create([
            'title'     => 'Schedule',
            'route'     => 'schedule.index',
            'icon'      => 'calendar-check',
            'parent_id' => $att->id,
            'order'     => 2,
            'roles'     => ['supervisor','admin'],
        ]);

        //
        // 6) Payroll — hr + admin
        //
        $pay = Sidebar::create([
            'title' => 'Payroll',
            'icon'  => 'currency-dollar',
            'order' => 6,
            'roles' => ['hr','admin'],
        ]);
        Sidebar::create([
            'title'     => 'Payroll List',
            'route'     => 'payroll.index',
            'icon'      => 'file-earmark-text',
            'parent_id' => $pay->id,
            'order'     => 1,
            'roles'     => ['hr','admin'],
        ]);
        Sidebar::create([
            'title'     => 'Salary Rates',
            'route'     => 'designations.index',
            'icon'      => 'percent',
            'parent_id' => $pay->id,
            'order'     => 2,
            'roles'     => ['hr','admin'],
        ]);
        Sidebar::create([
            'title'     => 'Deductions',
            'route'     => 'deductions.index',
            'icon'      => 'credit-card-2-back',
            'parent_id' => $pay->id,
            'order'     => 3,
            'roles'     => ['hr','admin'],
        ]);

        //
        // 7) Performance Evaluation — supervisor + admin
        //
        $pe = Sidebar::create([
            'title' => 'Performance Evaluation',
            'icon'  => 'bar-chart-line',
            'order' => 7,
            'roles' => ['supervisor','admin'],
        ]);
        Sidebar::create([
            'title'     => 'Performance Plans',
            'route'     => 'plans.index',
            'icon'      => 'list-task',
            'parent_id' => $pe->id,
            'order'     => 1,
            'roles'     => ['supervisor','admin'],
        ]);

        //
        // 8) Reports — all except employee
        //
        Sidebar::create([
            'title' => 'Reports',
            'route' => 'reports.index',
            'icon'  => 'file-earmark-bar-graph',
            'order' => 8,
            'roles' => ['admin','hr','timekeeper','supervisor'],
        ]);


        // 9) My Dashboard (Employee)
        Sidebar::create([
            'title' => 'My Dashboard',
            'route' => 'dashboard.employee',
            'icon'  => 'house',
            'order' => 9,
            'roles' => ['employee','admin'],
        ]);

        // 10) Self Service (parent)
        $ss = Sidebar::create([
            'title' => 'Self Service',
            'icon'  => 'person-circle',
            'order' => 10,
            'roles' => ['employee','admin'],
        ]);

        Sidebar::create([
            'title'     => 'My Leave Requests',
            'route'     => 'leaves.index',
            'icon'      => 'calendar',
            'parent_id' => $ss->id,
            'order'     => 1,
            'roles'     => ['employee','admin'],
        ]);

        Sidebar::create([
            'title'     => 'Payslips',
            'route'     => 'payslips.index',
            'icon'      => 'file-earmark-text',
            'parent_id' => $ss->id,
            'order'     => 2,
            'roles'     => ['employee','admin'],
        ]);
    }
}
