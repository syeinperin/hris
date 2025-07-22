<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Discipline\ActionType;
use App\Models\Discipline\InfractionInvestigator;
use App\Models\Discipline\InfractionReport;
use Spatie\Permission\Models\Role; // or however you resolve “supervisor” role
use App\Models\User;

class DisciplineSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Disable FK checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // 2) Truncate all dependent tables in correct order
        DB::table('disciplinary_actions')->truncate();
        DB::table('infraction_investigators')->truncate();
        DB::table('action_types')->truncate();

        // 3) Re-enable FK checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // 4) Seed Action Types
        $types = [
            ['code'=>'VW',   'description'=>'Verbal Warning',        'severity_level'=>'Minor',        'outcome'=>'Verbal Warning',      'leave_policy'=>null,              'leave_days'=>0,  'active'=>true],
            ['code'=>'WW',   'description'=>'Written Warning',       'severity_level'=>'Moderate',     'outcome'=>'Written Reprimand',   'leave_policy'=>null,              'leave_days'=>0,  'active'=>true],
            ['code'=>'SWP',  'description'=>'Suspension With Pay',   'severity_level'=>'Serious',      'outcome'=>'Suspension',          'leave_policy'=>'Administrative Leave','leave_days'=>15, 'active'=>true],
            ['code'=>'SWOP', 'description'=>'Suspension Without Pay','severity_level'=>'Unacceptable', 'outcome'=>'Suspension',          'leave_policy'=>null,              'leave_days'=>0,  'active'=>true],
            ['code'=>'TERM', 'description'=>'Termination',           'severity_level'=>'Unacceptable', 'outcome'=>'Discharge',           'leave_policy'=>null,              'leave_days'=>0,  'active'=>true],
        ];

        foreach ($types as $t) {
            ActionType::create($t);
        }

        // 5) Assign every “supervisor” user as investigator for each existing infraction
        $supervisorRole = Role::findByName('supervisor');
        $supervisorIds = $supervisorRole->users()->pluck('id');
        $reportIds     = InfractionReport::pluck('id');

        foreach ($reportIds as $infractionId) {
            foreach ($supervisorIds as $userId) {
                InfractionInvestigator::create([
                    'infraction_report_id' => $infractionId,
                    'user_id'              => $userId,
                ]);
            }
        }
    }
}
