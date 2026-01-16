<?php

declare(strict_types=1);

/**
 * Anchor Framework
 *
 * UserRole pivot model for the Permit package.
 *
 * @author BenIyke <beniyke34@gmail.com> | Twitter: @BigBeniyke
 */

namespace Permit\Models;

use Database\BaseModel;
use Database\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $role_id
 * @property-read Role $role
 */
class UserRole extends BaseModel
{
    protected string $table = 'permit_user_role';

    public bool $timestamps = false;

    protected array $fillable = [
        'user_id',
        'role_id',
    ];

    protected array $casts = [
        'id' => 'int',
        'user_id' => 'int',
        'role_id' => 'int',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
