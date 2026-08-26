<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\Exception;
use Laminas\Router\RouteBuilderInterface;
use Laminas\Router\RouteBuilderRegistry;
use Laminas\Router\RouteInterface;

use function is_array;

/**
 * @template TRoute of HttpRouteInterface
 * @implements RouteBuilderInterface<Chain<TRoute>>
 */
final readonly class ChainBuilder implements RouteBuilderInterface
{
    public function __construct(
        private RouteBuilderRegistry $routeBuilderRegistry,
    ) {
    }

    public function build(array $options = []): RouteInterface
    {
        if (! isset($options['routes']) || ! is_array($options['routes'])) {
            throw new Exception\InvalidArgumentException('Missing "routes" in options array');
        }

        /** @psalm-var array<non-empty-string, array|TRoute> $routes */
        $routes = $options['routes'];
        /** @psalm-var int|null $priority */
        $priority = $options['priority'] ?? null;
        /** @psalm-var array<string, string|int|float|null> $defaults */
        $defaults = $options['defaults'] ?? [];

        return new Chain(
            $this->routeBuilderRegistry,
            $routes,
            $defaults,
            $priority,
        );
    }
}
