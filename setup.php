<?php

declare(strict_types=1);

/**
 * Anchor Framework
 *
 * Permit Package Setup
 *
 * Role-Based Access Control (RBAC) and Attribute-Based Access Control (ABAC)
 * for the Anchor Framework.
 *
 * @author BenIyke <beniyke34@gmail.com> | Twitter: @BigBeniyke
 */

return [
    'providers' => [
        Permit\Providers\PermitServiceProvider::class,
    ],
    'middleware' => [],
];
