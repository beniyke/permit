<?php

declare(strict_types=1);

/**
 * Anchor Framework
 *
 * UserPermission pivot model for direct user-permission assignments.
 * Supports both grant and deny types for fine-grained control.
 *
 * @author BenIyke <beniyke34@gmail.com> | Twitter: @BigBeniyke
 */

namespace Permit\Models;

use Database\BaseModel;
use Database\Relations\BelongsTo;
use Permit\Enums\PermissionType;

/**
 * @property int            $id
 * @property int            $user_id
 * @property int            $permission_id
 * @property PermissionType $type
 * @property-read Permission $permission
 */
class UserPermission extends BaseModel
{
    protected string $table = 'permit_user_permission';

    public bool $timestamps = false;

    protected array $fillable = [
        'user_id',
        'permission_id',
        'type',
    ];

    protected array $casts = [
        'user_id' => 'int',
        'permission_id' => 'int',
        'type' => PermissionType::class,
    ];

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'permission_id');
    }

    public function isGrant(): bool
    {
        return $this->type === PermissionType::GRANT;
    }

    public function isDeny(): bool
    {
        return $this->type === PermissionType::DENY;
    }
}
