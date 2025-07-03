<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Role;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition()
    {
        static $names = ['hr','employee','supervisor'];

        // Create a unique role name from the list
        return [
            'name' => array_shift($names) 
                      ?? $this->faker->unique()->word(),
        ];
    }
}
