<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Holiday;
use Carbon\Carbon;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $year = Carbon::now()->year;

        // Calculate movable dates
        $easterTimestamp = easter_date($year);
        $easter          = Carbon::createFromTimestamp($easterTimestamp);
        $maundyThursday  = $easter->copy()->subDays(3);
        $goodFriday      = $easter->copy()->subDays(2);

        // Define all the regular national holidays
        $holidays = [
            ['name' => "New Year’s Day",        'date' => Carbon::create($year, 1,  1)],
            ['name' => "Maundy Thursday",       'date' => $maundyThursday],
            ['name' => "Good Friday",           'date' => $goodFriday],
            ['name' => "Araw ng Kagitingan",    'date' => Carbon::create($year, 4,  9)],
            ['name' => "Labor Day",             'date' => Carbon::create($year, 5,  1)],
            ['name' => "Independence Day",      'date' => Carbon::create($year, 6, 12)],
            ['name' => "Ninoy Aquino Day",      'date' => Carbon::create($year, 8, 21)],
            ['name' => "National Heroes Day",   'date' => Carbon::parse("last monday of august $year")],
            ['name' => "Bonifacio Day",         'date' => Carbon::create($year,11, 30)],
            ['name' => "Christmas Day",         'date' => Carbon::create($year,12, 25)],
            ['name' => "Rizal Day",             'date' => Carbon::create($year,12, 30)],
        ];

        foreach ($holidays as $h) {
            Holiday::updateOrCreate(
                ['date' => $h['date']->toDateString()],
                ['name' => $h['name']]
            );
        }
    }
}
