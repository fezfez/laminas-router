<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\Exception\InvalidArgumentException;
use Laminas\Router\RoutePluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

use function is_array;
use function assert;

final readonly class ChainFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, string $requestedName, ?array $options = null): Chain
    {
        $options = $options ?? [];
        $routes = $options['routes'] ?? null;
        $routePlugins = $container->get(RoutePluginManager::class);
        assert($routePlugins instanceof RoutePluginManager);
        if (! is_array($routes)) {
            throw new InvalidArgumentException('Missing "routes" in options array');
        }
        if (! $routePlugins instanceof RoutePluginManager) {
            throw new InvalidArgumentException('Missing "route_plugins" in options array');
        }
        return new Chain($routePlugins, $routes, $options['defaults'] ?? [], $options['priority'] ?? null);
    }
}
