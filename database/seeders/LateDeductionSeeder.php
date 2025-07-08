<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LateDeduction;

class LateDeductionSeeder extends Seeder
{
    public function run(): void
    {
        // Remove any existing rows so we can re-seed safely:
        LateDeduction::truncate();

        LateDeduction::insert([
            // 1) Up to 15 minutes: .25 hour
            [
                'mins_min'   => 1,
                'mins_max'   => 15,
                'multiplier' => 0.25,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // 2) 16–30 minutes: .50 hour
            [
                'mins_min'   => 16,
                'mins_max'   => 30,
                'multiplier' => 0.50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // 3) 31–45 minutes: .75 hour
            [
                'mins_min'   => 31,
                'mins_max'   => 45,
                'multiplier' => 0.75,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // 4) 46–60 minutes: 1.00 hour
            [
                'mins_min'   => 46,
                'mins_max'   => 60,
                'multiplier' => 1.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
