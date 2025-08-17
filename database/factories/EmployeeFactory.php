<?php
// database/factories/EmployeeFactory.php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Employee;
use App\Models\User;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Schedule;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition()
    {
        // 1) Pick or create a department
        $department = Department::inRandomOrder()->first()
            ?? Department::create([
                'name' => $this->faker->unique()->company(),
            ]);

        // 2) Pick or create a designation
        $designation = Designation::inRandomOrder()->first()
            ?? Designation::create([
                'name' => $this->faker->unique()->jobTitle(),
            ]);

        // 3) Pick or create an 8-hour shift schedule
        $in  = $this->faker->time('H:i:s');
        $out = date('H:i:s', strtotime($in) + 8 * 3600);
        $schedule = Schedule::inRandomOrder()->first()
            ?? Schedule::create([
                'name'     => substr($in,0,5) . '–' . substr($out,0,5),
                'time_in'  => $in,
                'time_out' => $out,
                'rest_day' => null,
            ]);

        // 4) Create a user
        $user = User::factory()->create();

        // 5) Employment dates
        $type      = $this->faker->randomElement([
                        'regular','casual','project','seasonal','fixed-term','probationary'
                     ]);
        $startDate = $this->faker->dateTimeBetween('-5 years', 'now')->format('Y-m-d');
        $endDate   = $type === 'probationary'
                        ? $this->faker->dateTimeBetween('tomorrow','+90 days')->format('Y-m-d')
                        : $this->faker
                               ->dateTimeBetween("{$startDate} +1 month", '+5 years')
                               ->format('Y-m-d');

        return [
            'user_id'               => $user->id,
            'employee_code'         => $this->faker->unique()->regexify('EMP[0-9]{5}'),
            'first_name'            => $this->faker->firstName(),
            'middle_name'           => $this->faker->optional()->firstName(),
            'last_name'             => $this->faker->lastName(),
            'name'                  => null, // auto‐filled by model if null
            'email'                 => $user->email,
            'gender'                => $this->faker->randomElement(['male','female','other']),
            'dob'                   => $this->faker->date('Y-m-d','-18 years'),
            'status'                => $this->faker->randomElement(['active','inactive','pending']),
            'employment_type'       => $type,
            'employment_start_date' => $startDate,
            'employment_end_date'   => $endDate,
            'current_address'       => $this->faker->address(),
            'permanent_address'     => $this->faker->optional()->address(),
            'father_name'           => $this->faker->name('male'),
            'mother_name'           => $this->faker->name('female'),
            'previous_company'      => $this->faker->company(),
            'job_title'             => $this->faker->jobTitle(),
            'years_experience'      => $this->faker->numberBetween(0,20),
            'nationality'           => $this->faker->country(),

            'department_id'         => $department->id,
            'designation_id'        => $designation->id,
            'schedule_id'           => $schedule->id,

            // Expanded fingerprint ID: "FP" + 5 digits (10000–99999)
            'fingerprint_id'        => 'FP' . $this->faker->unique()->numberBetween(10000, 99999),
            'profile_picture'       => null,

            // Benefits
            'gsis_id_no'            => $this->faker->optional()->numerify('GSIS#####'),
            'pagibig_id_no'         => $this->faker->optional()->numerify('PAGIBIG#####'),
            'philhealth_tin_id_no'  => $this->faker->optional()->bothify('PH####-#####'),
            'sss_no'                => $this->faker->optional()->numerify('SSS-#########'),
            'tin_no'                => $this->faker->optional()->numerify('TIN#########'),
            'agency_employee_no'    => $this->faker->optional()->bothify('AG###??'),
        ];
    }
}
