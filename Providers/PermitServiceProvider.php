<?php

declare(strict_types=1);

/**
 * Anchor Framework
 *
 * Service provider for the Permit package.
 * Registers all services and commands.
 *
 * @author BenIyke <beniyke34@gmail.com> | Twitter: @BigBeniyke
 */

namespace Permit\Providers;

use Core\Services\ServiceProvider;
use Permit\Services\GateManagerService;
use Permit\Services\PermissionManagerService;
use Permit\Services\PermitManagerService;
use Permit\Services\RoleManagerService;

class PermitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container->singleton(GateManagerService::class);
        $this->container->singleton(PermissionManagerService::class);
        $this->container->singleton(RoleManagerService::class);
        $this->container->singleton(PermitManagerService::class);
    }

    public function boot(): void
    {
        // Any boot logic can be added here
    }
}
