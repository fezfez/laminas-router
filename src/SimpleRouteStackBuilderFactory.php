<?php

declare(strict_types=1);

namespace Laminas\Router;

use Psr\Container\ContainerInterface;

use function assert;

/**
 * @internal
 *
 * @psalm-internal \Laminas\Router
 * @psalm-internal \LaminasTest\Router
 */
final readonly class SimpleRouteStackBuilderFactory
{
    public function __invoke(
        ContainerInterface $container
    ): SimpleRouteStackBuilder {
        $registry = $container->get(RouteBuilderRegistry::class);
        assert($registry instanceof RouteBuilderRegistry);

        return new SimpleRouteStackBuilder($registry);
    }
}
