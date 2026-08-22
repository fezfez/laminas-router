<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\ConfigProvider;
use Laminas\Router\RouteStackInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Translator\TranslatorInterface;
use Psr\Container\ContainerInterface;

use function assert;

/**
 * @internal
 *
 * @psalm-internal LaminasTest\Router
 * @psalm-import-type RouterConfigShape from ConfigProvider
 */
final readonly class HttpRouterFactory implements FactoryInterface
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
        $router = $container->get(RouteStackInterface::class);

        assert($router instanceof RouteStackInterface);

        return $router;
    }
}
