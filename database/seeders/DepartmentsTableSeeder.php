<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentsTableSeeder extends Seeder
{
    public function run()
    {
        $departments = [
            'Office', 'Production', 'Spinning', 'Weaving',
            'Finishing', 'Inspection', 'Packing', 'Engineering'
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(['name' => $dept]);
        }
    }
}
