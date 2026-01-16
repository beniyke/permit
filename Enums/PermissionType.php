<?php

declare(strict_types=1);

/**
 * Anchor Framework
 *
 * Permission type enum for granting or denying permissions.
 *
 * @author BenIyke <beniyke34@gmail.com> | Twitter: @BigBeniyke
 */

namespace Permit\Enums;

enum PermissionType: string
{
    case GRANT = 'grant';
    case DENY = 'deny';
}
