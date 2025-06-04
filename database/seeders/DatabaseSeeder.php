<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

// ← Add these use‐lines for each of your seeders:
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\DepartmentsTableSeeder;
use Database\Seeders\DesignationsTableSeeder;
use Database\Seeders\SchedulesTableSeeder;
use Database\Seeders\SidebarSeeder;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\SssContributionSeeder;
use Database\Seeders\PagibigContributionSeeder;
use Database\Seeders\PhilhealthContributionSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1) Core lookup data:
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
        ]);

        // 2) Then spin up 50 test employees via factory:
        \App\Models\Employee::factory()
            ->count(50)
            ->create();
    }
}
