<?php

declare(strict_types=1);

/**
 * Anchor Framework
 *
 * Exception thrown when authorization fails.
 *
 * @author BenIyke <beniyke34@gmail.com> | Twitter: @BigBeniyke
 */

namespace Permit\Exceptions;

use Exception;

class UnauthorizedException extends Exception
{
    public function __construct(string $message = 'This action is unauthorized.', int $code = 403)
    {
        parent::__construct($message, $code);
    }
}
