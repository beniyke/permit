<?php

declare(strict_types=1);

/**
 * Anchor Framework
 *
 * Role model for the Permit package.
 * Supports hierarchical inheritance through parent_id.
 *
 * @author BenIyke <beniyke34@gmail.com> | Twitter: @BigBeniyke
 */

namespace Permit\Models;

use Database\BaseModel;
use Database\Collections\ModelCollection;
use Database\Relations\BelongsTo;
use Database\Relations\BelongsToMany;
use Database\Relations\HasMany;
use Helpers\DateTimeHelper;
use InvalidArgumentException;

/**
 * @property int             $id
 * @property string          $name
 * @property string          $slug
 * @property ?string         $description
 * @property ?int            $parent_id
 * @property ?DateTimeHelper $created_at
 * @property ?DateTimeHelper $updated_at
 * @property-read ?Role $parent
 * @property-read ModelCollection $children
 * @property-read ModelCollection $permissions
 */
class Role extends BaseModel
{
    protected string $table = 'permit_role';

    protected array $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
    ];

    protected array $casts = [
        'id' => 'int',
        'parent_id' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Role::class, 'parent_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'permit_role_permission',
            'role_id',
            'permission_id'
        )->withoutTimestamps();
    }

    public function allPermissions(): ModelCollection
    {
        $permissions = $this->permissions()->get();

        if ($this->parent_id && config('permit.role_hierarchy', true)) {
            $parent = $this->parent()->first();
            if ($parent) {
                $parentPermissions = $parent->allPermissions();
                $permissions = $permissions->merge($parentPermissions);
            }
        }

        return $permissions->unique('id');
    }

    public function hasPermission(string|Permission $permission): bool
    {
        $slug = $permission instanceof Permission ? $permission->slug : $permission;

        return $this->allPermissions()
            ->filter(fn (Permission $p) => $p->slug === $slug)
            ->isNotEmpty();
    }

    /**
     * Assign a permission to this role.
     */
    public function givePermission(Permission|string $permission): void
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->first();
            if (!$permission) {
                throw new InvalidArgumentException("Permission '{$permission}' not found.");
            }
        }

        if (!$this->hasDirectPermission($permission)) {
            RolePermission::create([
                'role_id' => $this->id,
                'permission_id' => $permission->id,
            ]);
        }
    }

    /**
     * Revoke a permission from this role.
     */
    public function revokePermission(Permission|string $permission): void
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->first();
            if (!$permission) {
                return;
            }
        }

        RolePermission::where('role_id', $this->id)
            ->where('permission_id', $permission->id)
            ->delete();
    }

    public function hasDirectPermission(Permission|string $permission): bool
    {
        $slug = $permission instanceof Permission ? $permission->slug : $permission;

        return $this->permissions()->get()
            ->filter(fn (Permission $p) => $p->slug === $slug)
            ->isNotEmpty();
    }

    /**
     * Sync permissions for this role.
     */
    public function syncPermissions(array $permissions): void
    {
        // Remove all existing permissions
        RolePermission::where('role_id', $this->id)->delete();

        foreach ($permissions as $permission) {
            $this->givePermission($permission);
        }
    }

    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    /**
     * Get all ancestor roles (parent, grandparent, etc.).
     */
    public function ancestors(): array
    {
        $ancestors = [];
        $current = $this;

        while ($current->parent_id) {
            $parent = $current->parent()->first();
            if ($parent) {
                $ancestors[] = $parent;
                $current = $parent;
            } else {
                break;
            }
        }

        return $ancestors;
    }

    /**
     * Get all descendant roles (children, grandchildren, etc.).
     */
    public function descendants(): array
    {
        $descendants = [];
        $children = $this->children()->get();

        foreach ($children as $child) {
            $descendants[] = $child;
            $descendants = array_merge($descendants, $child->descendants());
        }

        return $descendants;
    }
}
