<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 1) core lookups
        $this->call([
            RolesTableSeeder::class,
            DepartmentsTableSeeder::class,
            DesignationsTableSeeder::class,   // ← newly added
            SchedulesTableSeeder::class,      // ← newly added
            SidebarSeeder::class,
            UsersTableSeeder::class,
        ]);
    
        // 2) then spin up your test employees
        \App\Models\Employee::factory()
            ->count(50)
            ->create();
    }    
}
