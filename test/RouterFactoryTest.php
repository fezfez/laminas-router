<?php

declare(strict_types=1);

namespace LaminasTest\Router;

use Laminas\Router\ConfigProvider;
use Laminas\Router\Http\HttpRouterFactory;
use Laminas\Router\Http\TreeRouteStack;
use Laminas\Router\RoutePluginManager;
use Laminas\Router\RouteStackInterface;
use Laminas\Router\RouterFactory;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function array_merge_recursive;

/**
 * @see ConfigInterface
 *
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 */
class RouterFactoryTest extends TestCase
{
    /** @psalm-var ServiceManagerConfiguration */
    protected array $defaultServiceConfig;
    protected HttpRouterFactory|RouterFactory $factory;

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
                TreeRouteStack::class => \Laminas\Router\Http\TreeRouteStackFactory::class,
                RouteStackInterface::class => RouterFactory::class,
                TestAsset\Router::class => InvokableFactory::class,
                // @phpcs:disable Generic.Files.LineLength.TooLong
                RoutePluginManager::class => static fn(ContainerInterface $services): RoutePluginManager => new RoutePluginManager($services),
            ],
        ];

        $this->factory = new RouterFactory();
    }

    public function testFactoryCanCreateRouterBasedOnConfiguredName(): void
    {
        $config   = array_merge_recursive($this->defaultServiceConfig, [
            'services' => [
                'config' => [
                    'router' => [
                        'router_class' => TestAsset\Router::class,
                    ],
                ],
            ],
        ]);
        $services = new ServiceManager($config);

        $router = $this->factory->__invoke($services, 'router');
        $this->assertInstanceOf(TestAsset\Router::class, $router);
    }

    public function testFactoryCanCreateRouterWhenOnlyHttpRouterConfigPresent(): void
    {
        $config   = array_merge_recursive($this->defaultServiceConfig, [
            'services' => [
                'config' => [
                    'router' => [
                        'router_class' => TestAsset\Router::class,
                    ],
                ],
            ],
        ]);
        $services = new ServiceManager($config);

        $router = $this->factory->__invoke($services, 'router');
        $this->assertInstanceOf(TestAsset\Router::class, $router);
    }

    public function testDefaultConfig(): void
    {
        $services = new ServiceManager((new ConfigProvider())->getDependencyConfig());

        $router = $this->factory->__invoke($services, 'router');
        $this->assertInstanceOf(TreeRouteStack::class, $router);
    }
}
