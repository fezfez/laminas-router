<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\Exception;
use Laminas\Router\RouteBuilderInterface;
use Laminas\Router\RouteBuilderRegistry;
use Laminas\Router\RouteInterface;

use function assert;
use function is_array;
use function is_bool;
use function is_int;

/**
 * @template TRoute of HttpRouteInterface
 * @implements RouteBuilderInterface<Part<TRoute>>
 */
final readonly class PartBuilder implements RouteBuilderInterface
{
    public function __construct(
        private RouteBuilderRegistry $routeBuilderRegistry,
    ) {
    }

    public function build(array $options = []): RouteInterface
    {
        /** @psalm-var array|TRoute|null $routes */
        $routes = $options['route'] ?? null;
        /** @psalm-var bool|null $mayTerminate */
        $mayTerminate = $options['may_terminate'] ?? false;
        /** @var array<non-empty-string, TRoute|array> $childRoutes */
        $childRoutes = $options['child_routes'] ?? [];
        /** @psalm-var array<string, string|int|float|null> $defaults */
        $defaults = $options['defaults'] ?? [];

        if (! is_array($routes) && ! $routes instanceof HttpRouteInterface) {
            throw new Exception\InvalidArgumentException('Missing "route" in options array');
        }

        assert(is_bool($mayTerminate));

        /** @psalm-var int|null $priority */
        $priority = $options['priority'] ?? null;

        return new Part(
            $this->routeBuilderRegistry,
            $routes,
            $defaults,
            is_int($priority) ? $priority : null,
            $mayTerminate,
            $childRoutes,
        );
    }
}
