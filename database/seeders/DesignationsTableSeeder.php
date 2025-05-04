<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Designation;

class DesignationsTableSeeder extends Seeder
{
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
            'PPC/Put‑Up',
            'Spinning',
        ];

        foreach ($items as $name) {
            Designation::updateOrCreate(
                ['name' => $name],
                [
                    // write into the actual column name:
                    'rate_per_hour' => in_array($name, ['Supervisor','Accounting']) ? 80 : 65,
                ]
            );
        }
    }
}
