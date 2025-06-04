<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PhilhealthContributionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('philhealth_contributions')->truncate();

        $data = [
            // As of 2025: 4.5% of monthly salary; split 50/50 employee and employer
            ['range_min' => 0, 'range_max' => 999999.99, 'rate_percent' => 4.5],
        ];

        DB::table('philhealth_contributions')->insert($data);
    }
}
