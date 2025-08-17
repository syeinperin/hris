<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PerformanceItem;

class PerformanceItemsSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Job Understanding',       'weight' => 10, 'description' => 'Knows how to do the job completely and correctly'],
            ['name' => 'Job Skills',              'weight' => 15, 'description' => 'Possesses the skills necessary to accomplish the job'],
            ['name' => 'Growth',                  'weight' => 10, 'description' => 'Progress in overall ability and professionalism'],
            ['name' => 'Accuracy / Quality',      'weight' => 15, 'description' => 'Accuracy, completeness, and timeliness of work'],
            ['name' => 'Productivity',            'weight' => 15, 'description' => 'Output compared to expectations'],
            ['name' => 'Dependability',           'weight' => 15, 'description' => 'Punctuality, attendance, reliability'],
            ['name' => 'Leadership',              'weight' => 10, 'description' => 'Demonstrates leadership in team/company'],
            ['name' => 'Attitude',                'weight' => 5,  'description' => 'Positive attitude and enthusiasm'],
            ['name' => 'Teamwork / Cooperation',  'weight' => 5,  'description' => 'Works well with others'],
        ];

        foreach ($items as $i => $row) {
            PerformanceItem::updateOrCreate(
                ['name' => $row['name']],
                $row + ['display_order' => $i + 1, 'is_active' => true]
            );
        }
    }
}
