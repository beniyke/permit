<?php

declare(strict_types=1);

/**
 * Anchor Framework
 *
 * Service provider for the Permit package.
 * Registers all services and commands.
 *
 * @author BenIyke <beniyke34@gmail.com> | Twitter: @BigBeniyke
 */

namespace Permit\Providers;

use App\Models\User;
use Core\Services\ServiceProvider;
use Database\Collections\ModelCollection;
use Permit\Models\Permission;
use Permit\Models\Role;
use Permit\Models\UserRole;
use Permit\Services\GateManagerService;
use Permit\Services\PermissionManagerService;
use Permit\Services\PermitManagerService;
use Permit\Services\RoleManagerService;

class PermitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container->singleton(GateManagerService::class);
        $this->container->singleton(PermissionManagerService::class);
        $this->container->singleton(RoleManagerService::class);
        $this->container->singleton(PermitManagerService::class);
    }

    public function boot(): void
    {
        $this->registerUserMacros();
    }

    protected function registerUserMacros(): void
    {
        $container = $this->container;

        User::macro('roles', function () {
            $userRoles = UserRole::where('user_id', $this->id)->get();
            $roles = [];
            foreach ($userRoles as $ur) {
                $role = $ur->role()->first();
                if ($role) {
                    $roles[] = $role;
                }
            }

            return new ModelCollection($roles);
        });

        User::macro('hasRole', function (string|Role $role) {
            $slug = $role instanceof Role ? $role->slug : $role;

            return $this->roles()->contains(fn ($r) => $r->slug === $slug);
        });

        User::macro('hasRoles', function () {
            return $this->roles()->isNotEmpty();
        });

        User::macro('assignRole', function (string|Role $role) use ($container) {
            $container->get(RoleManagerService::class)->assignToUser($this, $role);

            return $this;
        });

        User::macro('removeRole', function (string|Role $role) use ($container) {
            $container->get(RoleManagerService::class)->revokeFromUser($this, $role);

            return $this;
        });

        User::macro('syncRoles', function (array $roles) use ($container) {
            $container->get(RoleManagerService::class)->syncForUser($this, $roles);

            return $this;
        });

        User::macro('isSuperAdmin', function () use ($container) {
            return $container->get(PermitManagerService::class)->isSuperAdmin($this);
        });

        User::macro('roleNames', function () {
            $roles = implode(' ', $this->roles()->map(fn ($r) => $r->name));

            return $roles;
        });

        User::macro('role', function () {
            return $this->roles()->first();
        });

        User::macro('hasPermission', function (string $permission) use ($container) {
            return $container->get(PermitManagerService::class)->can($this, $permission);
        });

        User::macro('givePermissionTo', function (string|Permission $permission) use ($container) {
            $container->get(PermissionManagerService::class)->grantToUser($this, $permission);

            return $this;
        });

        User::macro('denyPermissionTo', function (string|Permission $permission) use ($container) {
            $container->get(PermissionManagerService::class)->denyToUser($this, $permission);

            return $this;
        });

        User::macro('revokePermissionTo', function (string|Permission $permission) use ($container) {
            $container->get(PermissionManagerService::class)->revokeFromUser($this, $permission);

            return $this;
        });

        User::macro('syncPermissions', function (array $grants, array $denies = []) use ($container) {
            $container->get(PermissionManagerService::class)->syncForUser($this, $grants, $denies);

            return $this;
        });

        User::macro('getDirectPermissions', function () use ($container) {
            return $container->get(PermitManagerService::class)->getUserPermissions($this);
        });

        User::macro('can', function (string $ability, mixed $resource = null) use ($container) {
            return $container->get(PermitManagerService::class)->can($this, $ability, $resource);
        });
    }
}
