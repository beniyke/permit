<?php

declare(strict_types=1);

/**
 * Anchor Framework
 *
 * Core authorization manager for the Permit package.
 * Handles permission checks, gate definitions, and caching.
 *
 * @author BenIyke <beniyke34@gmail.com> | Twitter: @BigBeniyke
 */

namespace Permit\Services;

use App\Models\User;
use App\Services\Auth\Interfaces\AuthServiceInterface;
use Core\Services\ConfigServiceInterface;
use Permit\Enums\PermissionType;
use Permit\Exceptions\UnauthorizedException;
use Permit\Models\Permission;
use Permit\Models\Role;
use Permit\Models\UserPermission;
use Permit\Models\UserRole;

class PermitManagerService
{
    private array $gates = [];

    private array $cachedPermissions = [];

    public function __construct(
        private readonly ConfigServiceInterface $config,
        private readonly GateManagerService $gateManager
    ) {
    }

    public function can(User $user, string $ability, mixed $resource = null): bool
    {
        // Super admin bypass
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if ($this->hasExplicitDeny($user, $ability)) {
            return false;
        }

        if ($gateResult = $this->gateManager->check($ability, $user, $resource)) {
            return $gateResult === true;
        }

        if ($this->hasExplicitGrant($user, $ability)) {
            return true;
        }

        // Check role-based permissions (including inherited)
        return $this->hasRolePermission($user, $ability);
    }

    public function authorize(string $ability, mixed $resource = null): void
    {
        $user = $this->getCurrentUser();

        if (!$user) {
            throw new UnauthorizedException('Unauthenticated.');
        }

        if (!$this->can($user, $ability, $resource)) {
            throw new UnauthorizedException(
                "You are not authorized to perform this action: {$ability}"
            );
        }
    }

    public function isSuperAdmin(User $user): bool
    {
        $superAdminRole = $this->config->get('permit.super_admin_role', 'super-admin');

        return $this->hasRole($user, $superAdminRole);
    }

    public function hasRole(User $user, string|Role $role): bool
    {
        $roleSlug = $role instanceof Role ? $role->slug : $role;
        $userRoles = $this->getUserRoles($user);

        foreach ($userRoles as $userRole) {
            if ($userRole->slug === $roleSlug) {
                return true;
            }
        }

        return false;
    }

    public function hasAnyRole(User $user, array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($user, $role)) {
                return true;
            }
        }

        return false;
    }

    public function hasAllRoles(User $user, array $roles): bool
    {
        foreach ($roles as $role) {
            if (!$this->hasRole($user, $role)) {
                return false;
            }
        }

        return true;
    }

    public function getUserRoles(User $user): array
    {
        $userRoles = UserRole::where('user_id', $user->id)->get();
        $roles = [];

        foreach ($userRoles as $userRole) {
            $role = $userRole->role()->first();
            if ($role) {
                $roles[] = $role;

                // Include ancestor roles if hierarchy is enabled
                if ($this->config->get('permit.role_hierarchy', true)) {
                    $roles = array_merge($roles, $role->ancestors());
                }
            }
        }

        return array_unique($roles, SORT_REGULAR);
    }

    public function getUserPermissions(User $user): array
    {
        $permissions = [];

        $roles = $this->getUserRoles($user);
        foreach ($roles as $role) {
            $rolePermissions = $role->allPermissions();
            foreach ($rolePermissions as $permission) {
                $permissions[$permission->slug] = $permission;
            }
        }

        // Get direct user permissions (grants)
        $directGrants = UserPermission::where('user_id', $user->id)
            ->where('type', PermissionType::GRANT)
            ->get();

        foreach ($directGrants as $grant) {
            $permission = $grant->permission()->first();
            if ($permission) {
                $permissions[$permission->slug] = $permission;
            }
        }

        // Remove explicit denies
        $directDenies = UserPermission::where('user_id', $user->id)
            ->where('type', PermissionType::DENY)
            ->get();

        foreach ($directDenies as $deny) {
            $permission = $deny->permission()->first();
            if ($permission) {
                unset($permissions[$permission->slug]);
            }
        }

        return array_values($permissions);
    }

    private function hasExplicitDeny(User $user, string $ability): bool
    {
        $permission = Permission::findBySlug($ability);

        if (!$permission) {
            return false;
        }

        return UserPermission::where('user_id', $user->id)
            ->where('permission_id', $permission->id)
            ->where('type', PermissionType::DENY)
            ->exists();
    }

    private function hasExplicitGrant(User $user, string $ability): bool
    {
        $permission = Permission::findBySlug($ability);

        if (!$permission) {
            return false;
        }

        return UserPermission::where('user_id', $user->id)
            ->where('permission_id', $permission->id)
            ->where('type', PermissionType::GRANT)
            ->exists();
    }

    private function hasRolePermission(User $user, string $ability): bool
    {
        $roles = $this->getUserRoles($user);

        foreach ($roles as $role) {
            if ($role->hasPermission($ability)) {
                return true;
            }
        }

        return false;
    }

    public function sync(array $rolesWithPermissions): void
    {
        foreach ($rolesWithPermissions as $roleSlug => $data) {
            $role = Role::findBySlug($roleSlug);

            if (!$role) {
                $role = Role::create([
                    'slug' => $roleSlug,
                    'name' => $data['name'] ?? ucfirst($roleSlug),
                    'description' => $data['description'] ?? null,
                    'parent_id' => null,
                ]);

                // Handle parent role
                if (isset($data['inherits'])) {
                    $parentRole = Role::findBySlug($data['inherits']);
                    if ($parentRole) {
                        $role->update(['parent_id' => $parentRole->id]);
                    }
                }
            }

            // Sync permissions
            if (isset($data['permissions'])) {
                $permissions = [];
                foreach ($data['permissions'] as $permSlug) {
                    $permission = Permission::findOrCreate($permSlug);
                    $permissions[] = $permission;
                }
                $role->syncPermissions($permissions);
            }
        }
    }

    public function clearCache(): void
    {
        $this->cachedPermissions = [];
    }

    public function analytics(): PermitAnalyticsService
    {
        return new PermitAnalyticsService();
    }

    private function getCurrentUser(): ?User
    {
        return resolve(AuthServiceInterface::class)->user();
    }
}
