<?php

declare(strict_types=1);

/**
 * Anchor Framework
 *
 * Permission model for the Permit package.
 *
 * @author BenIyke <beniyke34@gmail.com> | Twitter: @BigBeniyke
 */

namespace Permit\Models;

use Database\BaseModel;
use Database\Collections\ModelCollection;
use Database\Relations\BelongsToMany;
use Helpers\DateTimeHelper;

/**
 * @property int             $id
 * @property string          $name
 * @property string          $slug
 * @property ?string         $description
 * @property ?string         $group
 * @property ?DateTimeHelper $created_at
 * @property ?DateTimeHelper $updated_at
 * @property-read ModelCollection $roles
 */
class Permission extends BaseModel
{
    protected string $table = 'permit_permission';

    protected array $fillable = [
        'name',
        'slug',
        'description',
        'group',
    ];

    protected array $casts = [
        'id' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'permit_role_permission',
            'permission_id',
            'role_id'
        )->withoutTimestamps();
    }

    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    public static function findOrCreate(string $slug, ?string $name = null, ?string $description = null): self
    {
        $permission = static::findBySlug($slug);

        if (!$permission) {
            $permission = static::create([
                'slug' => $slug,
                'name' => $name ?? self::generateNameFromSlug($slug),
                'description' => $description,
            ]);
        }

        return $permission;
    }

    protected static function generateNameFromSlug(string $slug): string
    {
        return ucwords(str_replace(['-', '_', '.'], ' ', $slug));
    }

    public static function grouped(): array
    {
        $permissions = static::all();
        $grouped = [];

        foreach ($permissions as $permission) {
            $group = $permission->group ?? 'general';
            if (!isset($grouped[$group])) {
                $grouped[$group] = [];
            }
            $grouped[$group][] = $permission;
        }

        return $grouped;
    }
}
