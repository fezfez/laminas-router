<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\RouterConfigTrait;
use Laminas\Router\RouteStackInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * @internal
 *
 * @psalm-internal LaminasTest\Router
 * @psalm-internal Laminas\Router
 */
final class HttpRouterFactory implements FactoryInterface
{
    use RouterConfigTrait;

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

        return $this->createRouter($class, $config, $container);
    }
}
