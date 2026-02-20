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

use App\Models\User;
use InvalidArgumentException;
use Permit\Exceptions\RoleNotFoundException;
use Permit\Models\Permission;
use Permit\Models\Role;
use Permit\Services\PermissionManagerService;
use Permit\Services\RoleManagerService;

class RoleBuilder
{
    private ?int $id = null;

    private ?string $slug = null;

    private ?string $name = null;

    private ?string $description = null;

    private ?Role $parent = null;

    private array $permissions = [];

    private ?User $assignTo = null;

    public function __construct(
        private readonly RoleManagerService $manager,
        private readonly PermissionManagerService $permission_service
    ) {
    }

    public function id(int $id): self
    {
        $this->id = $id;

        return $this;
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

    /**
     * Assign the resulting role to a user.
     */
    public function assign(User $user): self
    {
        $this->assignTo = $user;

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
                $permission = $this->permission_service->findOrCreate($permission);
            }
            $role->givePermission($permission);
        }

        if ($this->assignTo) {
            $this->manager->assignToUser($this->assignTo, $role);
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

    public function update(): Role
    {
        if (!$this->id && !$this->slug) {
            throw new InvalidArgumentException('Role ID or Slug is required for update.');
        }

        $role = $this->id ? Role::find($this->id) : Role::findBySlug($this->slug);

        if (!$role) {
            $identifier = $this->id ?: $this->slug;
            throw new RoleNotFoundException("Role '{$identifier}' not found for update.");
        }

        $role->name = $this->name ?? $role->name;
        $role->slug = $this->slug ?? $role->slug;
        $role->description = $this->description ?? $role->description;
        $role->parent_id = $this->parent?->id ?? $role->parent_id;

        $role->save();

        if (!empty($this->permissions)) {
            $permissions = [];
            foreach ($this->permissions as $p) {
                $permissions[] = is_string($p) ? $this->permission_service->findOrCreate($p) : $p;
            }
            $role->syncPermissions($permissions);
        }

        if ($this->assignTo) {
            $this->manager->assignToUser($this->assignTo, $role);
        }

        return $role;
    }
}
