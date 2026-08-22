<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\Exception\InvalidArgumentException;
use Laminas\Router\RoutePluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

use function is_bool;
use function assert;

final readonly class PartFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, string $requestedName, ?array $options = null): Part
    {
        $options = $options ?? [];
        $route = $options['route'] ?? null;
        $routePlugins = $container->get(RoutePluginManager::class);
        assert($routePlugins instanceof RoutePluginManager);
        $mayTerminate = $options['may_terminate'] ?? false;
        if ($route === null) {
            throw new InvalidArgumentException('Missing "route" in options array');
        }
        if (! $routePlugins instanceof RoutePluginManager || ! is_bool($mayTerminate)) {
            throw new InvalidArgumentException('Invalid route options');
        }
        return new Part(
            $routePlugins,
            $route,
            $options['defaults'] ?? [],
            $options['priority'] ?? null,
            $mayTerminate,
            $options['child_routes'] ?? [],
        );
    }
}
