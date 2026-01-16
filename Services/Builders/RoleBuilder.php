<?php

declare(strict_types=1);

/**
 * Anchor Framework
 *
 * Fluent builder for creating roles.
 *
 * @author BenIyke <beniyke34@gmail.com> | Twitter: @BigBeniyke
 */

namespace Permit\Services\Builders;

use InvalidArgumentException;
use Permit\Models\Permission;
use Permit\Models\Role;
use Permit\Services\RoleManagerService;

class RoleBuilder
{
    private ?string $slug = null;

    private ?string $name = null;

    private ?string $description = null;

    private ?Role $parent = null;

    private array $permissions = [];

    public function __construct(
        private readonly RoleManagerService $manager
    ) {
    }

    public function slug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Set the parent role (for hierarchy inheritance).
     */
    public function inherits(string|Role $parent): self
    {
        if (is_string($parent)) {
            $parent = Role::findBySlug($parent);
        }

        $this->parent = $parent;

        return $this;
    }

    /**
     * Assign permissions to this role.
     */
    public function permissions(array $permissions): self
    {
        $this->permissions = $permissions;

        return $this;
    }

    public function permission(string|Permission $permission): self
    {
        $this->permissions[] = $permission;

        return $this;
    }

    public function create(?string $slug = null, ?string $name = null): Role
    {
        $slug = $slug ?? $this->slug;
        $name = $name ?? $this->name ?? ucfirst($slug);

        if (!$slug) {
            throw new InvalidArgumentException('Role slug is required.');
        }

        $role = $this->manager->create(
            $slug,
            $name,
            $this->description,
            $this->parent
        );

        // Assign permissions
        foreach ($this->permissions as $permission) {
            if (is_string($permission)) {
                $permission = Permission::findOrCreate($permission);
            }
            $role->givePermission($permission);
        }

        return $role;
    }

    public function findOrCreate(?string $slug = null, ?string $name = null): Role
    {
        $slug = $slug ?? $this->slug;
        $name = $name ?? $this->name ?? ucfirst($slug);

        if (!$slug) {
            throw new InvalidArgumentException('Role slug is required.');
        }

        $role = Role::findBySlug($slug);

        if ($role) {
            return $role;
        }

        return $this->create($slug, $name);
    }
}
