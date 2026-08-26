<?php

declare(strict_types=1);

namespace Laminas\Router;

use Laminas\Router\Http\TreeRouteStack;
use Psr\Container\ContainerInterface;

use function assert;
use function is_array;

/**
 * Creates a route stack service via its registered builder.
 *
 * @internal
 *
 * @psalm-internal \Laminas\Router
 * @psalm-internal \LaminasTest\Router
 */
final readonly class RouteStackServiceFactory
{
    public function __invoke(
        ContainerInterface $container
    ): RouteStackInterface {
        $config = $container->has('config') ? $container->get('config') : [];
        assert(is_array($config));

        /** @var array<string, mixed> $routerConfig */
        $routerConfig = is_array($config['router'] ?? null) ? $config['router'] : [];

        /** @var class-string<RouteStackInterface> $class */
        $class = $routerConfig['router_class'] ?? TreeRouteStack::class;

        /** @var array<string, mixed> $stackOptions */
        $stackOptions = [
            'routes'         => $routerConfig['routes'] ?? [],
            'default_params' => $routerConfig['default_params'] ?? [],
        ];

        $registry = $container->get(RouteBuilderRegistry::class);
        assert($registry instanceof RouteBuilderRegistry);

        $router = $registry->build($class, $stackOptions);
        assert($router instanceof RouteStackInterface);

        return $router;
    }
}
