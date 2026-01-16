<?php

declare(strict_types=1);

/**
 * Anchor Framework
 *
 * Permission management service for the Permit package.
 *
 * @author BenIyke <beniyke34@gmail.com> | Twitter: @BigBeniyke
 */

namespace Permit\Services;

use App\Models\User;
use Permit\Enums\PermissionType;
use Permit\Exceptions\PermissionAlreadyExistsException;
use Permit\Exceptions\PermissionNotFoundException;
use Permit\Models\Permission;
use Permit\Models\UserPermission;
use Permit\Services\Builders\PermissionBuilder;

class PermissionManagerService
{
    /**
     * Create a new PermissionBuilder instance.
     */
    public function make(): PermissionBuilder
    {
        return new PermissionBuilder($this);
    }

    public function create(
        string $slug,
        string $name,
        ?string $description = null,
        ?string $group = null
    ): Permission {
        $existing = Permission::findBySlug($slug);

        if ($existing) {
            throw new PermissionAlreadyExistsException("Permission '{$slug}' already exists.");
        }

        return Permission::create([
            'slug' => $slug,
            'name' => $name,
            'description' => $description,
            'group' => $group,
        ]);
    }

    /**
     * Find a permission by slug.
     */
    public function find(string $slug): ?Permission
    {
        return Permission::findBySlug($slug);
    }

    public function findOrFail(string $slug): Permission
    {
        $permission = $this->find($slug);

        if (!$permission) {
            throw new PermissionNotFoundException("Permission '{$slug}' not found.");
        }

        return $permission;
    }

    public function findOrCreate(string $slug, ?string $name = null, ?string $description = null): Permission
    {
        return Permission::findOrCreate($slug, $name, $description);
    }

    public function all(): array
    {
        return Permission::all()->toArray();
    }

    public function grouped(): array
    {
        return Permission::grouped();
    }

    public function delete(string|Permission $permission): bool
    {
        if (is_string($permission)) {
            $permission = $this->findOrFail($permission);
        }

        // Remove from user direct permissions
        UserPermission::where('permission_id', $permission->id)->delete();

        return $permission->delete();
    }

    /**
     * Grant a permission directly to a user.
     */
    public function grantToUser(User $user, string|Permission $permission): void
    {
        if (is_string($permission)) {
            $permission = $this->findOrFail($permission);
        }

        // Remove any deny first
        UserPermission::where('user_id', $user->id)
            ->where('permission_id', $permission->id)
            ->delete();

        UserPermission::create([
            'user_id' => $user->id,
            'permission_id' => $permission->id,
            'type' => PermissionType::GRANT,
        ]);
    }

    /**
     * Deny a permission directly to a user (overrides role permissions).
     */
    public function denyToUser(User $user, string|Permission $permission): void
    {
        if (is_string($permission)) {
            $permission = $this->findOrFail($permission);
        }

        // Remove any grant first
        UserPermission::where('user_id', $user->id)
            ->where('permission_id', $permission->id)
            ->delete();

        UserPermission::create([
            'user_id' => $user->id,
            'permission_id' => $permission->id,
            'type' => PermissionType::DENY,
        ]);
    }

    /**
     * Revoke a direct permission from a user.
     */
    public function revokeFromUser(User $user, string|Permission $permission): void
    {
        if (is_string($permission)) {
            $permission = $this->find($permission);
            if (!$permission) {
                return;
            }
        }

        UserPermission::where('user_id', $user->id)
            ->where('permission_id', $permission->id)
            ->delete();
    }

    /**
     * Sync direct permissions for a user.
     */
    public function syncForUser(User $user, array $grants, array $denies = []): void
    {
        // Remove all existing direct permissions
        UserPermission::where('user_id', $user->id)->delete();

        // Add grants
        foreach ($grants as $permission) {
            $this->grantToUser($user, $permission);
        }

        // Add denies
        foreach ($denies as $permission) {
            $this->denyToUser($user, $permission);
        }
    }

    /**
     * Create multiple permissions at once.
     */
    public function createMany(array $permissions): array
    {
        $created = [];

        foreach ($permissions as $slug => $data) {
            if (is_string($data)) {
                $slug = $data;
                $data = [];
            }

            $created[] = $this->findOrCreate(
                $slug,
                $data['name'] ?? null,
                $data['description'] ?? null
            );
        }

        return $created;
    }
}
