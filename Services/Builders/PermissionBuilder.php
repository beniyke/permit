<?php

declare(strict_types=1);

/**
 * Anchor Framework
 *
 * Fluent builder for creating permissions.
 *
 * @author BenIyke <beniyke34@gmail.com> | Twitter: @BigBeniyke
 */

namespace Permit\Services\Builders;

use InvalidArgumentException;
use Permit\Models\Permission;
use Permit\Services\PermissionManagerService;

class PermissionBuilder
{
    private ?string $slug = null;

    private ?string $name = null;

    private ?string $description = null;

    private ?string $group = null;

    public function __construct(
        private readonly PermissionManagerService $manager
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

    public function group(string $group): self
    {
        $this->group = $group;

        return $this;
    }

    public function create(?string $slug = null, ?string $name = null): Permission
    {
        $slug = $slug ?? $this->slug;
        $name = $name ?? $this->name ?? ucwords(str_replace(['-', '_', '.'], ' ', $slug));

        if (!$slug) {
            throw new InvalidArgumentException('Permission slug is required.');
        }

        return $this->manager->create(
            $slug,
            $name,
            $this->description,
            $this->group
        );
    }

    public function findOrCreate(?string $slug = null, ?string $name = null): Permission
    {
        $slug = $slug ?? $this->slug;
        $name = $name ?? $this->name;

        if (!$slug) {
            throw new InvalidArgumentException('Permission slug is required.');
        }

        return Permission::findOrCreate($slug, $name, $this->description);
    }
}
