<?php

declare(strict_types=1);

/**
 * Anchor Framework
 *
 * Exception thrown when a permission already exists.
 *
 * @author BenIyke <beniyke34@gmail.com> | Twitter: @BigBeniyke
 */

namespace Permit\Exceptions;

use Exception;

class PermissionAlreadyExistsException extends Exception
{
    public function __construct(string $message = 'Permission already exists.', int $code = 409)
    {
        parent::__construct($message, $code);
    }
}
