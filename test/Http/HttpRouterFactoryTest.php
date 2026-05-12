<?php

declare(strict_types=1);

namespace LaminasTest\Router\Http;

use Laminas\Router\Http\HttpRouterFactory;
use Laminas\Router\RoutePluginManager;
use LaminasTest\Router\RouterFactoryTest as TestCase;
use Psr\Container\ContainerInterface;

final class HttpRouterFactoryTest extends TestCase
{
    public function setUp(): void
    {
        $this->defaultServiceConfig = [
            'services'  => [
                'config' => [
                    'router' => [
                        'route_plugins' => RoutePluginManager::class,
                    ],
                ],
            ],
            'factories' => [
                // @phpcs:disable Generic.Files.LineLength.TooLong
                RoutePluginManager::class => static fn(ContainerInterface $services): RoutePluginManager => new RoutePluginManager($services),
            ],
        ];

        $this->factory = new HttpRouterFactory();
    }
}
