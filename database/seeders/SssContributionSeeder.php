<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SssContributionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('sss_contributions')->truncate();

        $data = [
            ['range_min' => 0,     'range_max' => 5249.99,  'employee_share' => 250,  'employer_share' => 510],
            ['range_min' => 5250,  'range_max' => 5749.99,  'employee_share' => 275,  'employer_share' => 562.50],
            ['range_min' => 5750,  'range_max' => 6249.99,  'employee_share' => 300,  'employer_share' => 615],
            ['range_min' => 6250,  'range_max' => 6749.99,  'employee_share' => 325,  'employer_share' => 667.50],
            ['range_min' => 6750,  'range_max' => 7249.99,  'employee_share' => 350,  'employer_share' => 720],
            ['range_min' => 7250,  'range_max' => 7749.99,  'employee_share' => 375,  'employer_share' => 772.50],
            ['range_min' => 7750,  'range_max' => 8249.99,  'employee_share' => 400,  'employer_share' => 825],
            ['range_min' => 8250,  'range_max' => 8749.99,  'employee_share' => 425,  'employer_share' => 877.50],
            ['range_min' => 8750,  'range_max' => 9249.99,  'employee_share' => 450,  'employer_share' => 930],
            ['range_min' => 9250,  'range_max' => 9749.99,  'employee_share' => 475,  'employer_share' => 982.50],
            ['range_min' => 9750,  'range_max' => 10249.99, 'employee_share' => 500,  'employer_share' => 1035],
            ['range_min' => 10250, 'range_max' => 10749.99, 'employee_share' => 525,  'employer_share' => 1087.50],
            ['range_min' => 10750, 'range_max' => 11249.99, 'employee_share' => 550,  'employer_share' => 1140],
            ['range_min' => 11250, 'range_max' => 11749.99, 'employee_share' => 575,  'employer_share' => 1192.50],
            ['range_min' => 11750, 'range_max' => 12249.99, 'employee_share' => 600,  'employer_share' => 1245],
            ['range_min' => 12250, 'range_max' => 12749.99, 'employee_share' => 625,  'employer_share' => 1297.50],
            ['range_min' => 12750, 'range_max' => 13249.99, 'employee_share' => 650,  'employer_share' => 1350],
            ['range_min' => 13250, 'range_max' => 13749.99, 'employee_share' => 675,  'employer_share' => 1402.50],
            ['range_min' => 13750, 'range_max' => 14249.99, 'employee_share' => 700,  'employer_share' => 1455],
            ['range_min' => 14250, 'range_max' => 14749.99, 'employee_share' => 725,  'employer_share' => 1507.50],
            ['range_min' => 14750, 'range_max' => 15249.99, 'employee_share' => 750,  'employer_share' => 1560],
            ['range_min' => 15250, 'range_max' => 15749.99, 'employee_share' => 775,  'employer_share' => 1612.50],
            ['range_min' => 15750, 'range_max' => 16249.99, 'employee_share' => 800,  'employer_share' => 1665],
            ['range_min' => 16250, 'range_max' => 16749.99, 'employee_share' => 825,  'employer_share' => 1717.50],
            ['range_min' => 16750, 'range_max' => 17249.99, 'employee_share' => 850,  'employer_share' => 1770],
            ['range_min' => 17250, 'range_max' => 17749.99, 'employee_share' => 875,  'employer_share' => 1822.50],
            ['range_min' => 17750, 'range_max' => 18249.99, 'employee_share' => 900,  'employer_share' => 1875],
            ['range_min' => 18250, 'range_max' => 18749.99, 'employee_share' => 925,  'employer_share' => 1927.50],
            ['range_min' => 18750, 'range_max' => 19249.99, 'employee_share' => 950,  'employer_share' => 1980],
            ['range_min' => 19250, 'range_max' => 19749.99, 'employee_share' => 975,  'employer_share' => 2032.50],
            ['range_min' => 19750, 'range_max' => 999999.99,'employee_share' => 1000, 'employer_share' => 2085],
        ];

        DB::table('sss_contributions')->insert($data);
    }
}
