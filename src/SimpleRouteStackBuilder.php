<?php

declare(strict_types=1);

namespace Laminas\Router;

/**
 * @template TRoute of RouteInterface
 * @implements RouteBuilderInterface<SimpleRouteStack<TRoute>>
 */
final readonly class SimpleRouteStackBuilder implements RouteBuilderInterface
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

        return new SimpleRouteStack(
            $this->routeBuilderRegistry,
            $routes,
            $defaultParams
        );
    }
}
