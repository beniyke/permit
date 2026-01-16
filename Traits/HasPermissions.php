<?php

declare(strict_types=1);

/**
 * Anchor Framework
 *
 * HasPermissions trait for User model integration.
 * Provides permission checking and direct permission assignment.
 *
 * @author BenIyke <beniyke34@gmail.com> | Twitter: @BigBeniyke
 */

namespace Permit\Traits;

use Permit\Models\Permission;
use Permit\Services\PermissionManagerService;
use Permit\Services\PermitManagerService;

trait HasPermissions
{
    public function can(string $ability, mixed $resource = null): bool
    {
        return resolve(PermitManagerService::class)->can($this, $ability, $resource);
    }

    public function cannot(string $ability, mixed $resource = null): bool
    {
        return !$this->can($ability, $resource);
    }

    public function hasPermission(string $permission): bool
    {
        return $this->can($permission);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Grant a permission directly to this user.
     */
    public function givePermissionTo(string|Permission $permission): self
    {
        resolve(PermissionManagerService::class)->grantToUser($this, $permission);

        return $this;
    }

    /**
     * Revoke a direct permission from this user.
     */
    public function revokePermissionTo(string|Permission $permission): self
    {
        resolve(PermissionManagerService::class)->revokeFromUser($this, $permission);

        return $this;
    }

    /**
     * Deny a permission (overrides role permissions).
     */
    public function denyPermissionTo(string|Permission $permission): self
    {
        resolve(PermissionManagerService::class)->denyToUser($this, $permission);

        return $this;
    }

    public function getAllPermissions(): array
    {
        return resolve(PermitManagerService::class)->getUserPermissions($this);
    }

    public function getPermissionNames(): array
    {
        return array_map(fn ($p) => $p->slug, $this->getAllPermissions());
    }

    /**
     * Sync direct permissions for this user.
     */
    public function syncPermissions(array $permissions): self
    {
        resolve(PermissionManagerService::class)->syncForUser($this, $permissions);

        return $this;
    }
}
