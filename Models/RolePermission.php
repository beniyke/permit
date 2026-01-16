<?php

declare(strict_types=1);

/**
 * Anchor Framework
 *
 * RolePermission pivot model for the Permit package.
 *
 * @author BenIyke <beniyke34@gmail.com> | Twitter: @BigBeniyke
 */

namespace Permit\Models;

use Database\BaseModel;
use Database\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $role_id
 * @property int $permission_id
 * @property-read Role $role
 * @property-read Permission $permission
 */
class RolePermission extends BaseModel
{
    protected string $table = 'permit_role_permission';

    public bool $timestamps = false;

    protected array $fillable = [
        'role_id',
        'permission_id',
    ];

    protected array $casts = [
        'id' => 'int',
        'role_id' => 'int',
        'permission_id' => 'int',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'permission_id');
    }
}
