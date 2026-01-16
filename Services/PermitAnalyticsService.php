<?php

declare(strict_types=1);

/**
 * Anchor Framework
 *
 * Analytics service for Permit package (RBAC).
 *
 * @author BenIyke <beniyke34@gmail.com> | Twitter: @BigBeniyke
 */

namespace Permit\Services;

use Permit\Models\Permission;
use Permit\Models\Role;
use Permit\Models\RolePermission;
use Permit\Models\UserPermission;
use Permit\Models\UserRole;

class PermitAnalyticsService
{
    public function getOverview(): array
    {
        return [
            'total_roles' => Role::count(),
            'total_permissions' => Permission::count(),
            'role_assignments' => UserRole::count(),
            'direct_permissions' => UserPermission::count(),
            'role_permission_mappings' => RolePermission::count(),
        ];
    }

    public function getRoleDistribution(): array
    {
        return UserRole::selectRaw('role_id, COUNT(*) as user_count')
            ->groupBy('role_id')
            ->orderBy('user_count', 'desc')
            ->get()
            ->all();
    }

    public function getMostUsedPermissions(int $limit = 20): array
    {
        // Count direct user permissions
        $directPermissions = UserPermission::selectRaw('permission_id, COUNT(*) as usage_count')
            ->groupBy('permission_id')
            ->get();

        // Count permissions via roles
        $rolePermissions = RolePermission::selectRaw('permission_id, COUNT(DISTINCT ur.user_id) as usage_count')
            ->join('permit_user_role as ur', 'permit_role_permission.role_id', '=', 'ur.role_id')
            ->groupBy('permission_id')
            ->get();

        // Merge and sum
        $result = [];

        foreach ($directPermissions as $dp) {
            $result[$dp->permission_id] = ($result[$dp->permission_id] ?? 0) + $dp->usage_count;
        }

        foreach ($rolePermissions as $rp) {
            $result[$rp->permission_id] = ($result[$rp->permission_id] ?? 0) + $rp->usage_count;
        }

        arsort($result);

        return array_slice($result, 0, $limit, true);
    }

    public function getRoleHierarchy(): array
    {
        $roles = Role::with('children')->whereNull('parent_id')->get();

        return $this->buildHierarchy($roles->all());
    }

    public function getUsersWithoutRoles(): int
    {
        // This would need the User model
        return 0;
    }

    public function getRolesWithMostPermissions(int $limit = 10): array
    {
        return RolePermission::selectRaw('role_id, COUNT(*) as permission_count')
            ->groupBy('role_id')
            ->orderBy('permission_count', 'desc')
            ->limit($limit)
            ->get()
            ->all();
    }

    public function getPermissionsByGroup(): array
    {
        $permissions = Permission::get();
        $grouped = [];

        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->slug);
            $group = $parts[0] ?? 'other';

            if (!isset($grouped[$group])) {
                $grouped[$group] = 0;
            }

            $grouped[$group]++;
        }

        arsort($grouped);

        return $grouped;
    }

    private function buildHierarchy(array $roles, int $depth = 0): array
    {
        $result = [];

        foreach ($roles as $role) {
            $item = [
                'id' => $role->id,
                'name' => $role->name,
                'depth' => $depth,
                'children' => [],
            ];

            if ($role->children && $role->children->isNotEmpty()) {
                $item['children'] = $this->buildHierarchy($role->children->all(), $depth + 1);
            }

            $result[] = $item;
        }

        return $result;
    }
}
