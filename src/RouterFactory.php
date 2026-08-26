<?php

declare(strict_types=1);

namespace Laminas\Router;

use Laminas\Router\Http\TreeRouteStack;
use Psr\Container\ContainerInterface;

use function assert;

/**
 * @internal
 *
 * @psalm-internal \Laminas\Router
 * @psalm-internal \LaminasTest\Router
 */
final readonly class RouterFactory
{
    /**
     * Create and return the router
     *
     * Delegates to the TreeRouteStack service.
     */
    public function __invoke(
        ContainerInterface $container
    ): RouteStackInterface {
        $router = $container->get(TreeRouteStack::class);

        assert($router instanceof RouteStackInterface);

        return $router;
    }
}
