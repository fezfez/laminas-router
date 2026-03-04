<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\RouteStackInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

use function class_exists;
use function sprintf;

/**
 * @internal
 *
 * @psalm-internal LaminasTest\Router
 * @psalm-internal Laminas\Router
 */
final class HttpRouterFactory implements FactoryInterface
{
    /**
     * Create and return the HTTP router
     *
     * Retrieves the "router" key of the Config service, and uses it
     * to instantiate the router. Uses the TreeRouteStack implementation by
     * default.
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null
    ): RouteStackInterface {
        $config = $container->has('config') ? $container->get('config') : [];

        // Defaults
        $class  = TreeRouteStack::class;
        $config = $config['router'] ?? [];

        // Obtain the configured router class, if any
        if (isset($config['router_class']) && class_exists($config['router_class'])) {
            $class = $config['router_class'];
        }

        // Inject the route plugins
        if (! isset($config['route_plugins'])) {
            $routePluginManager      = $container->get('RoutePluginManager');
            $config['route_plugins'] = $routePluginManager;
        }

        // Obtain an instance
        $factory = sprintf('%s::factory', $class);
        return $factory($config);
    }
}
