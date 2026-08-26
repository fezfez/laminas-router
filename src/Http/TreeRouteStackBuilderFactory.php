<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\RouteBuilderRegistry;
use Psr\Container\ContainerInterface;

use function assert;

/**
 * @internal
 *
 * @psalm-internal \Laminas\Router
 * @psalm-internal \LaminasTest\Router
 */
final readonly class TreeRouteStackBuilderFactory
{
    public function __invoke(
        ContainerInterface $container
    ): TreeRouteStackBuilder {
        $registry = $container->get(RouteBuilderRegistry::class);
        assert($registry instanceof RouteBuilderRegistry);

        return new TreeRouteStackBuilder($registry);
    }
}
