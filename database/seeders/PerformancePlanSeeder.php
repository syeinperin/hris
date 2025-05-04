<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PerformancePlan;
use App\Models\PerformancePlanItem;

class PerformancePlanSeeder extends Seeder
{
    public function run()
    {
        $plan = PerformancePlan::create([
            'name'       => 'Standard Annual Plan',
            'starts_at'  => '2025-01-01',
            'ends_at'    => '2025-12-31',
        ]);

        // Example KPIs & weights
        PerformancePlanItem::insert([
            ['performance_plan_id' => $plan->id, 'metric' => 'Revenue Growth', 'weight' => 40],
            ['performance_plan_id' => $plan->id, 'metric' => 'Customer Satisfaction', 'weight' => 30],
            ['performance_plan_id' => $plan->id, 'metric' => 'Operational Efficiency', 'weight' => 30],
        ]);
    }
}
