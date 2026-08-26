<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\RouteBuilderInterface;
use Laminas\Router\RouteBuilderRegistry;
use Laminas\Router\RouteInterface;

/**
 * @template TRoute of HttpRouteInterface
 * @implements RouteBuilderInterface<TreeRouteStack<TRoute>>
 */
final readonly class TreeRouteStackBuilder implements RouteBuilderInterface
{
    public function __construct(
        private RouteBuilderRegistry $routeBuilderRegistry,
    ) {
    }

    public function build(array $options = []): RouteInterface
    {
        /** @psalm-var array<non-empty-string, array|TRoute> $routes */
        $routes = $options['routes'] ?? [];
        /** @psalm-var array<string, string|int|float|null> $defaultParams */
        $defaultParams = $options['default_params'] ?? [];
        /** @psalm-var int|null $priority */
        $priority = $options['priority'] ?? null;

        return new TreeRouteStack(
            $this->routeBuilderRegistry,
            $routes,
            $defaultParams,
            $priority,
        );
    }
}
