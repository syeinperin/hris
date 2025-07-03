<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Employment-Type Action Map
    |--------------------------------------------------------------------------
    |
    | Defines which buttons appear for each employment_type on the
    | “Ending Soon” page. Each entry has:
    |  - label:  Button text
    |  - route:  Named route for the action
    |  - method: HTTP verb (patch | delete)
    |
    */

    'action_map' => [
        'probationary' => [
            ['label'=>'Promote to Regular','route'=>'employees.regularize','method'=>'patch'],
            ['label'=>'Reject Probation','route'=>'employees.rejectProbation','method'=>'delete'],
        ],

        'fixed-term' => [
            ['label'=>'Renew Contract','route'=>'employees.extendTerm','method'=>'patch'],
            ['label'=>'Terminate','route'=>'employees.terminate','method'=>'patch'],
        ],

        'seasonal' => [
            ['label'=>'Extend Seasonal','route'=>'employees.extendSeason','method'=>'patch'],
        ],

        'project' => [
            ['label'=>'Extend Project','route'=>'employees.extendProject','method'=>'patch'],
        ],

        'casual' => [
            ['label'=>'Extend Casual','route'=>'employees.extendCasual','method'=>'patch'],
        ],

        // regular ⇒ no actions
    ],

];
