<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Schedule;

class SchedulesTableSeeder extends Seeder
{
    public function run()
    {
        $shifts = [
            ['name' => '06:00–14:00', 'time_in' => '06:00:00', 'time_out' => '14:00:00'],
            ['name' => '14:00–22:00', 'time_in' => '14:00:00', 'time_out' => '22:00:00'],
        ];

        foreach ($shifts as $shift) {
            Schedule::updateOrCreate(
                ['name' => $shift['name']],
                ['time_in' => $shift['time_in'], 'time_out' => $shift['time_out']]
            );
        }
    }
}
