<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Permission & Role Models
    |--------------------------------------------------------------------------
    |
    | If you want to use your own models (e.g. in a different namespace),
    | specify them here.
    |
    */

    'models' => [

        'permission' => Spatie\Permission\Models\Permission::class,
        'role'       => Spatie\Permission\Models\Role::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    |
    | Here you can customize the table names that the package will use.
    | We’ve renamed Spatie’s default `roles` table to `permission_roles`
    | so it never collides with your existing `roles` table.
    |
    */

    
    'table_names' => [

        // point Spatie’s “roles” at our new table:
        'roles'                 => 'permission_roles',

        // and its permissions at its own table:
        'permissions'           => 'permissions',

        'model_has_permissions' => 'model_has_permissions',
        'model_has_roles'       => 'model_has_roles',
        'role_has_permissions'  => 'role_has_permissions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Column Names
    |--------------------------------------------------------------------------
    |
    | Customize the names of pivot columns, model morph keys, etc.
    |
    */

    'column_names' => [
        'role_pivot_key'       => null,
        'permission_pivot_key' => null,
        'model_morph_key'      => 'model_id',
        'team_foreign_key'     => 'team_id',
    ],

    /*
    |--------------------------------------------------------------------------
    | Register Gate Method
    |--------------------------------------------------------------------------
    */

    'register_permission_check_method' => true,

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    */

    'events_enabled' => false,

    /*
    |--------------------------------------------------------------------------
    | Teams
    |--------------------------------------------------------------------------
    */

    'teams'           => false,
    'team_foreign_key'=> 'team_id',
    'team_resolver'   => \Spatie\Permission\DefaultTeamResolver::class,

    /*
    |--------------------------------------------------------------------------
    | Passport Client Credentials Grant
    |--------------------------------------------------------------------------
    */

    'use_passport_client_credentials' => false,

    /*
    |--------------------------------------------------------------------------
    | Display Permission/Role in Exceptions
    |--------------------------------------------------------------------------
    */

    'display_permission_in_exception' => false,
    'display_role_in_exception'       => false,

    /*
    |--------------------------------------------------------------------------
    | Wildcard Permissions
    |--------------------------------------------------------------------------
    */

    'enable_wildcard_permission'      => false,
    // 'permission.wildcard_permission' => Spatie\Permission\WildcardPermission::class,

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */

    'cache' => [
        'expiration_time' => \DateInterval::createFromDateString('24 hours'),
        'key'             => 'spatie.permission.cache',
        'store'           => 'default',
    ],
];
