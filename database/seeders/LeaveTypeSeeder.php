<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeaveType;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'key'          => 'service',
                'name'         => 'Service Incentive Leave',
                'default_days' => 5,
            ],
            [
                'key'          => 'maternity',
                'name'         => 'Maternity Leave',
                'default_days' => 105,
            ],
            [
                'key'          => 'paternity',
                'name'         => 'Paternity Leave',
                'default_days' => 7,
            ],
        ];

        foreach ($types as $data) {
            LeaveType::updateOrCreate(
                ['key' => $data['key']],
                [
                    'name'         => $data['name'],
                    'default_days' => $data['default_days'],
                    'is_active'    => true,
                ]
            );
        }
    }
}
