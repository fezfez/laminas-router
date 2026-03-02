<?php

declare(strict_types=1);

namespace Laminas\Router;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\ServiceManager;
use Psr\Container\ContainerInterface;

/**
 * @internal
 *
 * @final
 *
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 */
final class RoutePluginManagerFactory implements FactoryInterface
{
    /**
     * Create and return a route plugin manager.
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null
    ): RoutePluginManager {
        $options ??= [];
        /** @psalm-var ServiceManagerConfiguration $options */
        return new RoutePluginManager($container, $options);
    }
}
