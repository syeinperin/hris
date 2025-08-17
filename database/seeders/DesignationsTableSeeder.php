<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Designation;

class DesignationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $items = [
            'Supervisor',
            'General Services',
            'Preparatory/Weaving',
            'Distribution',
            'Accounting',
            'Finishing',
            'Engineering',
            'PPC/Put-Up',
            'Spinning',
        ];

        foreach ($items as $name) {
            Designation::updateOrCreate(
                ['name' => $name],
                [
                    // Supervisor & Accounting at ₱80.000/hr; others at ₱65.375/hr
                    'rate_per_hour' => in_array($name, ['Supervisor', 'Accounting'])
                        ? 67.5
                        : 67.5,
                ]
            );
        }
    }
}
