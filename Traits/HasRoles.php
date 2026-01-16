<?php

declare(strict_types=1);

/**
 * Anchor Framework
 *
 * HasRoles trait for User model integration.
 * Provides role assignment and checking capabilities.
 *
 * @author BenIyke <beniyke34@gmail.com> | Twitter: @BigBeniyke
 */

namespace Permit\Traits;

use Database\Collections\ModelCollection;
use Permit\Models\Role;
use Permit\Models\UserRole;
use Permit\Services\RoleManagerService;

trait HasRoles
{
    public function roles(): ModelCollection
    {
        $userRoles = UserRole::where('user_id', $this->id)->get();
        $roles = new ModelCollection([]);

        foreach ($userRoles as $userRole) {
            $role = $userRole->role()->first();
            if ($role) {
                $roles->push($role);
            }
        }

        return $roles;
    }

    public function hasRole(string|Role $role): bool
    {
        $roleSlug = $role instanceof Role ? $role->slug : $role;

        return $this->roles()->filter(fn (Role $r) => $r->slug === $roleSlug)->isNotEmpty();
    }

    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    public function hasAllRoles(array $roles): bool
    {
        foreach ($roles as $role) {
            if (!$this->hasRole($role)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Assign a role to this user.
     */
    public function assignRole(string|Role $role): self
    {
        resolve(RoleManagerService::class)->assignToUser($this, $role);

        return $this;
    }

    /**
     * Assign multiple roles to this user.
     */
    public function assignRoles(array $roles): self
    {
        foreach ($roles as $role) {
            $this->assignRole($role);
        }

        return $this;
    }

    public function removeRole(string|Role $role): self
    {
        resolve(RoleManagerService::class)->revokeFromUser($this, $role);

        return $this;
    }

    /**
     * Sync roles for this user (replaces existing roles).
     */
    public function syncRoles(array $roles): self
    {
        resolve(RoleManagerService::class)->syncForUser($this, $roles);

        return $this;
    }

    public function getRoleNames(): array
    {
        return $this->roles()->map(fn (Role $role) => $role->slug)->toArray();
    }

    public function isSuperAdmin(): bool
    {
        $superAdminRole = config('permit.super_admin_role', 'super-admin');

        return $this->hasRole($superAdminRole);
    }
}
