<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\RoutePluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

use function assert;

final readonly class TreeRouteStackFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, string $requestedName, ?array $options = null): TreeRouteStack
    {
        $options = $options ?? [];
        $routePlugins = $container->get(RoutePluginManager::class);
        assert($routePlugins instanceof RoutePluginManager);
        return new TreeRouteStack(
            $routePlugins,
            $options['routes'] ?? [],
            $options['default_params'] ?? [],
            $options['priority'] ?? null,
        );
    }
}
