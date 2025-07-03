<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Employee;
use App\Models\User;
use App\Models\Schedule;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class PayrollCalendarTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function only_active_employees_are_listed_in_the_calendar()
    {
        // 1) Create the HR role & an HR user
        Role::create(['name' => 'hr']);
        $hr = User::factory()->create(['status' => 'active']);
        $hr->assignRole('hr');

        // 2) Create a schedule manually
        $schedule = Schedule::create([
            'name'     => 'Shift-One',
            'time_in'  => '06:00:00',
            'time_out' => '14:00:00',
        ]);

        // 3) Create employees: two active, one inactive
        $active1 = Employee::factory()->create([
            'status'      => 'active',
            'employment_start_date' => Carbon::today()->subMonth(),
            'employment_end_date'   => Carbon::today()->addMonth(),
            'schedule_id' => $schedule->id,
        ]);
        $active2 = Employee::factory()->create([
            'status'      => 'active',
            'employment_start_date' => Carbon::today()->subMonth(),
            'employment_end_date'   => Carbon::today()->addMonth(),
            'schedule_id' => $schedule->id,
        ]);
        $inactive = Employee::factory()->create([
            'status'      => 'inactive',
            'employment_start_date' => Carbon::today()->subMonths(2),
            'employment_end_date'   => Carbon::today()->subMonth(),
            'schedule_id' => $schedule->id,
        ]);

        // 4) Hit the calendar page as our HR user
        $response = $this
            ->actingAs($hr)
            ->get(route('payroll.calendar', ['month' => Carbon::now()->format('Y-m')]));

        $response->assertStatus(200);

        // 5) Grab the employees paginator passed to the view
        $listedIds = $response->viewData('employees')->pluck('id')->all();

        // 6) Assert we see the actives but not the inactive
        $this->assertContains($active1->id,   $listedIds);
        $this->assertContains($active2->id,   $listedIds);
        $this->assertNotContains($inactive->id, $listedIds);
    }
}
