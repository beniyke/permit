<?php

declare(strict_types=1);

/**
 * Anchor Framework
 *
 * Exception thrown when a role already exists.
 *
 * @author BenIyke <beniyke34@gmail.com> | Twitter: @BigBeniyke
 */

namespace Permit\Exceptions;

use Exception;

class RoleAlreadyExistsException extends Exception
{
    public function __construct(string $message = 'Role already exists.', int $code = 409)
    {
        parent::__construct($message, $code);
    }
}
