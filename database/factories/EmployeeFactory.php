<?php

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
    
        $department  = Department::inRandomOrder()->first()  ?: Department::factory()->create();
        $designation = Designation::inRandomOrder()->first() ?: Designation::factory()->create();
        $schedule    = Schedule::inRandomOrder()->first()    ?: Schedule::factory()->create();

        // Create a new User
        $user = User::factory()->create();

        // Pick a random employment type
        $employmentType = $this->faker->randomElement([
            'regular',
            'casual',
            'project',
            'seasonal',
            'fixed-term',
            'probationary',
        ]);

        // Determine the end date:
        // - If “probationary,” pick a date between tomorrow and +90 days.
        // - Otherwise, pick a date between +1 year and +5 years.
        if ($employmentType === 'probationary') {
            $endDate = $this->faker
                            ->dateTimeBetween('tomorrow', '+90 days')
                            ->format('Y-m-d');
        } else {
            $endDate = $this->faker
                            ->dateTimeBetween('+1 years', '+5 years')
                            ->format('Y-m-d');
        }

        return [
            'user_id'             => $user->id,
            'employee_code'       => $this->faker->unique()->regexify('EMP[0-9]{3}'),
            'name'                => $user->name,
            'first_name'          => $this->faker->firstName(),
            'middle_name'         => $this->faker->optional()->firstName(),
            'last_name'           => $this->faker->lastName(),
            'email'               => $user->email,
            'gender'              => $this->faker->randomElement(['male','female','other']),
            'dob'                 => $this->faker->date('Y-m-d', '-18 years'),
            'status'              => $this->faker->randomElement(['active','inactive','pending']),
            'employment_type'     => $employmentType,
            'employment_end_date' => $endDate,
            'current_address'     => $this->faker->address(),
            'permanent_address'   => $this->faker->optional()->address(),
            'father_name'         => $this->faker->name('male'),
            'mother_name'         => $this->faker->name('female'),
            'previous_company'    => $this->faker->company(),
            'job_title'           => $this->faker->jobTitle(),
            'years_experience'    => $this->faker->numberBetween(0,20),
            'nationality'         => $this->faker->country(),
            'department_id'       => $department->id,
            'designation_id'      => $designation->id,
            'schedule_id'         => $schedule->id,
            'fingerprint_id'      => $this->faker->unique()->numerify('FP###'),
            'profile_picture'     => null,
        ];
    }
}
