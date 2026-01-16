<?php

declare(strict_types=1);

/**
 * Anchor Framework
 *
 * Exception thrown when a permission is not found.
 *
 * @author BenIyke <beniyke34@gmail.com> | Twitter: @BigBeniyke
 */

namespace Permit\Exceptions;

use Exception;

class PermissionNotFoundException extends Exception
{
    public function __construct(string $message = 'Permission not found.', int $code = 404)
    {
        parent::__construct($message, $code);
    }
}
