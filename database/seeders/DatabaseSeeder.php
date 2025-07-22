<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Core lookup data
        $this->call([
            RolesTableSeeder::class,
            DepartmentsTableSeeder::class,
            DesignationsTableSeeder::class,
            SchedulesTableSeeder::class,
            SidebarSeeder::class,
            UsersTableSeeder::class,
            SssContributionSeeder::class,
            PagibigContributionSeeder::class,
            PhilhealthContributionSeeder::class,
            LeaveTypeSeeder::class,
            HolidaySeeder::class,         
            LoanTypeSeeder::class,
            LoanPlanSeeder::class,
            LateDeductionSeeder::class,
            DisciplineSeeder::class,
        ]);

        // 2) Bulk create employees
        \App\Models\Employee::factory()
            ->count(50)
            ->create();

        // 3) Now seed each employee’s allocations for the current year
        $this->call([
            LeaveAllocationSeeder::class,
        ]);
    }
}
