<?php

declare(strict_types=1);

/**
 * Anchor Framework
 *
 * Role management service for the Permit package.
 *
 * @author BenIyke <beniyke34@gmail.com> | Twitter: @BigBeniyke
 */

namespace Permit\Services;

use App\Models\User;
use Permit\Exceptions\RoleAlreadyExistsException;
use Permit\Exceptions\RoleNotFoundException;
use Permit\Models\Role;
use Permit\Models\UserRole;
use Permit\Services\Builders\RoleBuilder;

class RoleManagerService
{
    /**
     * Create a new RoleBuilder instance.
     */
    public function make(): RoleBuilder
    {
        return new RoleBuilder($this);
    }

    public function create(
        string $slug,
        string $name,
        ?string $description = null,
        ?Role $parent = null
    ): Role {
        $existing = Role::findBySlug($slug);

        if ($existing) {
            throw new RoleAlreadyExistsException("Role '{$slug}' already exists.");
        }

        return Role::create([
            'slug' => $slug,
            'name' => $name,
            'description' => $description,
            'parent_id' => $parent?->id,
        ]);
    }

    /**
     * Find a role by slug.
     */
    public function find(string $slug): ?Role
    {
        return Role::findBySlug($slug);
    }

    public function findOrFail(string $slug): Role
    {
        $role = $this->find($slug);

        if (!$role) {
            throw new RoleNotFoundException("Role '{$slug}' not found.");
        }

        return $role;
    }

    public function all(): array
    {
        return Role::all()->toArray();
    }

    public function delete(string|Role $role): bool
    {
        if (is_string($role)) {
            $role = $this->findOrFail($role);
        }

        // Remove user associations
        UserRole::where('role_id', $role->id)->delete();

        Role::where('parent_id', $role->id)->update(['parent_id' => null]);

        return $role->delete();
    }

    /**
     * Assign a role to a user.
     */
    public function assignToUser(User $user, string|Role $role): void
    {
        if (is_string($role)) {
            $role = $this->findOrFail($role);
        }

        $exists = UserRole::where('user_id', $user->id)
            ->where('role_id', $role->id)
            ->exists();

        if (!$exists) {
            UserRole::create([
                'user_id' => $user->id,
                'role_id' => $role->id,
            ]);
        }
    }

    public function revokeFromUser(User $user, string|Role $role): void
    {
        if (is_string($role)) {
            $role = $this->find($role);
            if (!$role) {
                return;
            }
        }

        UserRole::where('user_id', $user->id)
            ->where('role_id', $role->id)
            ->delete();
    }

    /**
     * Sync roles for a user.
     */
    public function syncForUser(User $user, array $roles): void
    {
        // Remove all existing roles
        UserRole::where('user_id', $user->id)->delete();

        foreach ($roles as $role) {
            $this->assignToUser($user, $role);
        }
    }

    public function getUsersWithRole(string|Role $role): array
    {
        if (is_string($role)) {
            $role = $this->findOrFail($role);
        }

        $userRoles = UserRole::where('role_id', $role->id)->get();
        $userIds = [];

        foreach ($userRoles as $userRole) {
            $userIds[] = $userRole->user_id;
        }

        if (empty($userIds)) {
            return [];
        }

        $userModel = config('permit.user_model', 'App\Models\User');

        return $userModel::whereIn('id', $userIds)->get()->toArray();
    }
}
