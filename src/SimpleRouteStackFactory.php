<?php

declare(strict_types=1);

namespace Laminas\Router;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

final readonly class SimpleRouteStackFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null
    ): SimpleRouteStack {
        $options = $options ?? [];
        $routePlugins = $container->get(RoutePluginManager::class);

        return new SimpleRouteStack(
            $routePlugins,
            $options['routes'] ?? [],
            $options['default_params'] ?? [],
        );
    }
}
