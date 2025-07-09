<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActionTypeSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // disable FK checks, truncate, then re-enable
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('action_types')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('action_types')->insert([
            [
                'code'           => 'VERBAL',
                'description'    => 'Verbal Warning',
                'severity_level' => 'Minor',
                'outcome'        => 'Verbal Warning',
                'leave_policy'   => null,
                'leave_days'     => 0,
                'active'         => true,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'code'           => 'WRITTEN',
                'description'    => 'Written Warning',
                'severity_level' => 'Moderate',
                'outcome'        => 'Written Reprimand',
                'leave_policy'   => null,
                'leave_days'     => 0,
                'active'         => true,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'code'           => 'SUSP_PAY',
                'description'    => 'Suspension With Pay',
                'severity_level' => 'Serious',
                'outcome'        => 'Suspension',
                'leave_policy'   => 'Admin Leave',
                'leave_days'     => 15,
                'active'         => true,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'code'           => 'SUSP_NOPAY',
                'description'    => 'Suspension Without Pay',
                'severity_level' => 'Unacceptable',
                'outcome'        => 'Suspension',
                'leave_policy'   => null,
                'leave_days'     => 0,
                'active'         => true,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'code'           => 'TERMINATION',
                'description'    => 'Termination',
                'severity_level' => 'Unacceptable',
                'outcome'        => 'Discharge',
                'leave_policy'   => null,
                'leave_days'     => 0,
                'active'         => true,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
        ]);
    }
}
