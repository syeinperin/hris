<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\PayrollController;
use App\Models\Employee;
use App\Models\Schedule;
use App\Models\Designation;
use App\Models\Department;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class PayrollCalculationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_calculates_late_deduction_prorated_per_minute()
    {
        $controller = new PayrollController;
        $late = $this->invokeProtected(
            $controller,
            'calculateProratedLate',
            [80.00, 15]    // ₱80/hr, 15 minutes late
        );

        $this->assertEquals(20.00, $late);
    }

    /** @test */
    public function it_builds_a_correct_payroll_row_for_example_scenario()
    {
        // 0) Seed a department (required for non-nullable FK)
        $dept = Department::create([
            'name' => 'Test Department',
            // add any other required fields here...
        ]);

        // 1) Seed a schedule (09:00–18:00)
        $schedule = Schedule::create([
            'name'     => 'Standard Day Shift',
            'time_in'  => '09:00:00',
            'time_out' => '18:00:00',
        ]);

        // 2) Seed a designation @ ₱80/hour
        $designation = Designation::create([
            'name'          => 'Test Rate',
            'rate_per_hour' => 80.00,
        ]);

        // 3) Create an employee with all non-nullable fields
        $emp = Employee::create([
            'employee_code'         => 'E001',
            'name'                  => 'Juan Dela Cruz',
            'email'                 => 'juan@example.com',
            'designation_id'        => $designation->id,
            'schedule_id'           => $schedule->id,
            'department_id'         => $dept->id,
            'status'                => 'active',
            'employment_type'       => 'regular',
            'employment_start_date' => '2025-01-01',
            'employment_end_date'   => '2025-12-31',
        ]);

        // 4) Create attendance: 2025-08-02 09:15 → 2025-08-02 23:00
        Attendance::create([
            'employee_id' => $emp->id,
            'time_in'     => Carbon::parse('2025-08-02 09:15:00'),
            'time_out'    => Carbon::parse('2025-08-02 23:00:00'),
        ]);

        // 5) Invoke buildPayrollRow()
        $controller = new PayrollController;
        $row = $this->invokeProtected(
            $controller,
            'buildPayrollRow',
            [$emp, Carbon::parse('2025-08-02')]
        );

        // 6) Assert the key numbers
        // - Worked hours: total on-clock = 13.75h
        // - OT hours: (13.75h − 9h shift − 1h grace) = 3.75h
        // - ND hours: 22:00→23:00 = 1.00h
        // - Late deduction: ₱80/60 × 15 = ₱20
        $this->assertEqualsWithDelta(13.75, $row['worked_hr'],      0.01, 'Worked hours');
        $this->assertEqualsWithDelta(3.75,  $row['ot_hr'],          0.01, 'OT hours');
        $this->assertEqualsWithDelta(1.00,  $row['nd_hr'],          0.01, 'ND hours (22–23)');
        $this->assertEquals(20.00, $row['late_deduction'],          'Late deduction');
    }

    /**
     * Helper to call protected/private methods.
     */
    protected function invokeProtected($object, string $method, array $args = [])
    {
        $ref = new \ReflectionClass(get_class($object));
        $m   = $ref->getMethod($method);
        $m->setAccessible(true);
        return $m->invokeArgs($object, $args);
    }
}
