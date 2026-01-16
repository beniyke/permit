<?php

declare(strict_types=1);

/**
 * Anchor Framework
 *
 * Permit Configuration
 *
 * Role-Based Access Control (RBAC) and Attribute-Based Access Control (ABAC)
 * configuration for the Anchor Framework.
 *
 * @author BenIyke <beniyke34@gmail.com> | Twitter: @BigBeniyke
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Super Admin Role
    |--------------------------------------------------------------------------
    |
    | Users with this role bypass all permission checks automatically.
    |
    */
    'super_admin_role' => 'super-admin',

    /*
    |--------------------------------------------------------------------------
    | Role Hierarchy
    |--------------------------------------------------------------------------
    |
    | When enabled, roles can inherit permissions from parent roles.
    | For example, if 'admin' has 'editor' as parent, 'admin' inherits
    | all 'editor' permissions.
    |
    */
    'role_hierarchy' => true,

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Permissions are cached for performance. Set the TTL in seconds.
    |
    */
    'cache' => [
        'enabled' => env('PERMIT_CACHE_ENABLED', true),
        'ttl' => env('PERMIT_CACHE_TTL', 3600), // 1 hour
        'prefix' => 'permit:',
    ],

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The fully qualified class name of the User model.
    |
    */
    'user_model' => App\Models\User::class,

    /*
    |--------------------------------------------------------------------------
    | Tables
    |--------------------------------------------------------------------------
    |
    | Database table names for the Permit package.
    |
    */
    'tables' => [
        'roles' => 'permit_roles',
        'permissions' => 'permit_permissions',
        'role_permissions' => 'permit_role_permission',
        'user_roles' => 'permit_user_roles',
        'user_permissions' => 'permit_user_permissions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Column Names
    |--------------------------------------------------------------------------
    |
    | Foreign key column names for polymorphic relationships.
    |
    */
    'columns' => [
        'user_id' => 'user_id',
        'role_id' => 'role_id',
        'permission_id' => 'permission_id',
    ],
];
