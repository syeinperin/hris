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
        // ensure at least one department/designation/schedule exists...
        $department  = Department::inRandomOrder()->first()  ?: Department::factory()->create();
        $designation = Designation::inRandomOrder()->first() ?: Designation::factory()->create();
        $schedule    = Schedule::inRandomOrder()->first()    ?: Schedule::factory()->create();

        // make a fresh user
        $user = User::factory()->create();

        return [
            'user_id'         => $user->id,
            // use faker->unique() to avoid dupes
            // after
            'employee_code' => $this->faker->unique()->regexify('EMP[0-9]{3}'),
            'name'            => $user->name,
            'first_name'      => $this->faker->firstName(),
            'middle_name'     => $this->faker->optional()->firstName(),
            'last_name'       => $this->faker->lastName(),
            'email'           => $user->email,
            'gender'          => $this->faker->randomElement(['male','female']),
            'dob'             => $this->faker->date('Y-m-d', '-18 years'),
            'status'          => $this->faker->randomElement(['active','inactive','pending']),
            'current_address' => $this->faker->address(),
            'permanent_address'=> $this->faker->optional()->address(),
            'father_name'     => $this->faker->name('male'),
            'mother_name'     => $this->faker->name('female'),
            'previous_company'=> $this->faker->company(),
            'job_title'       => $this->faker->jobTitle(),
            'years_experience'=> $this->faker->numberBetween(0,20),
            'nationality'     => $this->faker->country(),
            'department_id'   => $department->id,
            'designation_id'  => $designation->id,
            'schedule_id'     => $schedule->id,
            'fingerprint_id'  => $this->faker->unique()->numerify('FP###'),
            'profile_picture' => null,
        ];
    }
}
