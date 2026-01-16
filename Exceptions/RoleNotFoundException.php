<?php

declare(strict_types=1);

/**
 * Anchor Framework
 *
 * Exception thrown when a role is not found.
 *
 * @author BenIyke <beniyke34@gmail.com> | Twitter: @BigBeniyke
 */

namespace Permit\Exceptions;

use Exception;

class RoleNotFoundException extends Exception
{
    public function __construct(string $message = 'Role not found.', int $code = 404)
    {
        parent::__construct($message, $code);
    }
}
