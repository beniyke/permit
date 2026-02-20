<?php

declare(strict_types=1);

/**
 * Anchor Framework
 *
 * Static facade for permission and role operations.
 * Delegates calls to the underlying PermitManagerService.
 *
 * @author BenIyke <beniyke34@gmail.com> | Twitter: @BigBeniyke
 */

namespace Permit;

use App\Models\User;
use Permit\Models\Permission;
use Permit\Models\Role;
use Permit\Services\Builders\PermissionBuilder;
use Permit\Services\Builders\RoleBuilder;
use Permit\Services\GateManagerService;
use Permit\Services\PermissionManagerService;
use Permit\Services\PermitManagerService;
use Permit\Services\RoleManagerService;

class Permit
{
    public static function can(User $user, string $ability, mixed $resource = null): bool
    {
        return resolve(PermitManagerService::class)->can($user, $ability, $resource);
    }

    public static function cannot(User $user, string $ability, mixed $resource = null): bool
    {
        return !static::can($user, $ability, $resource);
    }

    /**
     * Authorize an ability or throw an exception.
     */
    public static function authorize(string $ability, mixed $resource = null): void
    {
        resolve(PermitManagerService::class)->authorize($ability, $resource);
    }

    /**
     * Start building a new role.
     */
    public static function role(): RoleBuilder
    {
        return resolve(RoleManagerService::class)->make();
    }

    /**
     * Start building a new permission.
     */
    public static function permission(): PermissionBuilder
    {
        return resolve(PermissionManagerService::class)->make();
    }

    /**
     * Get the RoleManagerService instance.
     */
    public static function roles(): RoleManagerService
    {
        return resolve(RoleManagerService::class);
    }

    /**
     * Get the PermissionManagerService instance.
     */
    public static function permissions(): PermissionManagerService
    {
        return resolve(PermissionManagerService::class);
    }

    /**
     * Get the GateManagerService instance.
     */
    public static function gates(): GateManagerService
    {
        return resolve(GateManagerService::class);
    }

    /**
     * Define a gate (authorization callback).
     */
    public static function define(string $ability, callable $callback): void
    {
        resolve(GateManagerService::class)->define($ability, $callback);
    }

    /**
     * Clear all cached permissions.
     */
    public static function clearCache(): void
    {
        resolve(PermitManagerService::class)->clearCache();
    }

    /**
     * Sync permissions from configuration.
     */
    public static function sync(array $rolesWithPermissions): void
    {
        resolve(PermitManagerService::class)->sync($rolesWithPermissions);
    }

    public static function getUsersWithRole(string|Role $role): array
    {
        return resolve(RoleManagerService::class)->getUsersWithRole($role);
    }

    public static function countUsersWithRole(string|Role $role): int
    {
        return resolve(RoleManagerService::class)->countUsersWithRole($role);
    }

    public static function hasUsers(string|Role $role): bool
    {
        return resolve(RoleManagerService::class)->hasUsers($role);
    }

    /**
     * Forward static calls to PermitManagerService.
     */
    public static function __callStatic(string $method, array $arguments): mixed
    {
        return resolve(PermitManagerService::class)->$method(...$arguments);
    }
}
