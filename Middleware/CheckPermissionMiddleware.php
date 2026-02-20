<?php

declare(strict_types=1);

namespace Permit\Middleware;

use Closure;
use Core\Middleware\MiddlewareInterface;
use Core\Services\ConfigServiceInterface;
use Helpers\Http\Flash;
use Helpers\Http\Request;
use Helpers\Http\Response;
use Helpers\String\Inflector;

class CheckPermissionMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ConfigServiceInterface $config,
        private readonly Flash $flash
    ) {
    }

    public function handle(Request $request, Response $response, Closure $next): mixed
    {
        $user = $request->user();

        if (! $user || ! $this->config->get('permit.smart_middleware.enabled')) {
            return $next($request, $response);
        }

        $segments = $request->segments();
        $map = $this->config->get('permit.smart_middleware.action_map', []);

        $actionRaw = null;
        $resourceRaw = null;

        // Scan from the end to find a recognized action and its preceding resource
        for ($i = count($segments) - 1; $i >= 1; $i--) {
            $current = strtolower($segments[$i]);
            if (isset($map[$current])) {
                $actionRaw = $current;
                $resourceRaw = strtolower($segments[$i - 1]);
                break;
            }
        }

        if ($resourceRaw && $actionRaw) {
            $resource = Inflector::pluralize($resourceRaw);
            $action = $map[$actionRaw] ?? $actionRaw;
            $permission = "{$resource}.{$action}";

            $request->setRouteContext('resource', $resource)
                ->setRouteContext('action', $action)
                ->setRoutePermission($permission);

            if (! $user->hasPermission($permission)) {
                if ($request->isAjax() || $request->wantsJson() || $request->routeIsApi()) {
                    return $response->json([
                        'error' => 'Unauthorized',
                        'message' => 'You do not have the required permission.',
                    ], 403);
                }

                $this->flash->error('Unauthorized access.');

                return $response->redirect($request->referer() ?? $request->fullRouteByName('home'));
            }
        }

        return $next($request, $response);
    }
}
