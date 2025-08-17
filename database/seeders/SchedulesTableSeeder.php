<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Schedule;

class SchedulesTableSeeder extends Seeder
{
    /**
     * Seed the schedules table with standard 8-hour shifts
     * and set Sunday as the default rest day.
     */
    public function run(): void
    {
        $shifts = [
            [
                'name'     => '06:00–14:00',
                'time_in'  => '06:00:00',
                'time_out' => '14:00:00',
                'rest_day' => 'Sunday',
            ],
            [
                'name'     => '14:00–22:00',
                'time_in'  => '14:00:00',
                'time_out' => '22:00:00',
                'rest_day' => 'Sunday',
            ],
            [
                // overnight shift (out is next day)
                'name'     => '22:00–06:00',
                'time_in'  => '22:00:00',
                'time_out' => '06:00:00',
                'rest_day' => 'Sunday',
            ],
        ];

        foreach ($shifts as $shift) {
            Schedule::updateOrCreate(
                ['name' => $shift['name']], // unique key
                [
                    'time_in'  => $shift['time_in'],
                    'time_out' => $shift['time_out'],
                    'rest_day' => $shift['rest_day'],
                ]
            );
        }
    }
}
