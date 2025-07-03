<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Yasumi\Yasumi;
use App\Models\Holiday;
use Carbon\Carbon;

class FetchHolidays extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'holidays:fetch {year?}';

    /**
     * The console command description.
     */
    protected $description = 'Fetch Philippine holidays for a given year and update the holidays table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $year = $this->argument('year') ?: Carbon::now()->year;
        $this->info("Fetching holidays for {$year}…");

        $list = Yasumi::create('Philippines', $year);
        foreach ($list as $h) {
            Holiday::updateOrCreate(
                ['date' => $h->format('Y-m-d')],
                [
                    'name'         => $h->getName(),
                    'type'         => $h->isOfficialHoliday() ? 'regular' : 'special',
                    'is_recurring' => true,
                ]
            );
        }

        $this->info('Done.');
    }
}
