<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LoanType;

class LoanTypeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            'Personal Loan',
            'Car Loan',
            'Housing Loan',
            'Education Loan',
        ];

        foreach ($types as $type) {
            LoanType::updateOrCreate(
                ['name' => $type],
                ['name' => $type]
            );
        }
    }
}
