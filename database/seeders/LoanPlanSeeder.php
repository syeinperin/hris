<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LoanPlan;

class LoanPlanSeeder extends Seeder
{
    public function run()
    {
        $plans = [
            // you decide the rate (%) for each term
            ['name'   => '6-month plan',  'months' =>  6,  'rate' => 3.0],
            ['name'   => '12-month plan', 'months' => 12,  'rate' => 5.0],
            ['name'   => '24-month plan', 'months' => 24,  'rate' => 8.0],
        ];

        foreach ($plans as $p) {
            LoanPlan::updateOrCreate(
                ['months' => $p['months']],
                [
                  'name'   => $p['name'],
                  'months' => $p['months'],
                  'rate'   => $p['rate'],
                ]
            );
        }
    }
}
