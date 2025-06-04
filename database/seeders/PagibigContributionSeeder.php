<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PagibigContributionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('pagibig_contributions')->truncate();

        $data = [
            // Up to ₱1,499.99: 1% employee, 2% employer (of monthly salary) but minimal amounts
            ['range_min' => 0,      'range_max' => 1499.99,   'employee_share' => 1,   'employer_share' => 2],
            // ₱1,500.00–₱4,999.99: 2% employee, 2% employer
            ['range_min' => 1500.0, 'range_max' => 4999.99,   'employee_share' => 2,   'employer_share' => 2],
            // ₱5,000.00 and above: capped ₱100 each
            ['range_min' => 5000.0, 'range_max' => 999999.99, 'employee_share' => 100, 'employer_share' => 100],
        ];

        DB::table('pagibig_contributions')->insert($data);
    }
}
