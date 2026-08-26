<?php

declare(strict_types=1);

namespace LaminasTest\Router;

use Laminas\Router\ConfigProvider;
use Laminas\Router\Http\TreeRouteStack;
use Laminas\Router\RouterFactory;
use Laminas\ServiceManager\ServiceManager;
use LaminasTest\Router\TestAsset\RouterBuilder;
use PHPUnit\Framework\TestCase;

use function array_merge;

final class RouterFactoryTest extends TestCase
{
    private RouterFactory $factory;

    public function setUp(): void
    {
        $this->factory = new RouterFactory();
    }

    /**
     * @param array<string, mixed> $routerOverrides
     */
    private function createServiceManager(array $routerOverrides = []): ServiceManager
    {
        $provider       = new ConfigProvider();
        $providerConfig = $provider();
        $dependencies   = $providerConfig['dependencies'];

        $dependencies['factories'][RouterBuilder::class] = static fn (): RouterBuilder => new RouterBuilder();

        /** @var array{
         *     router_class: class-string,
         *     route_builders: array<string, class-string>
         * } $routerConfig
         */
        $routerConfig = array_merge($providerConfig['router'], $routerOverrides);

        if (($routerOverrides['router_class'] ?? null) === TestAsset\Router::class) {
            $routerConfig['route_builders'][TestAsset\Router::class] = RouterBuilder::class;
        }

        $services = new ServiceManager($dependencies);
        $services->setService('config', ['router' => $routerConfig]);

        return $services;
    }

    public function testFactoryCanCreateRouterBasedOnConfiguredName(): void
    {
        $services = $this->createServiceManager([
            'router_class' => TestAsset\Router::class,
        ]);

        $router = $this->factory->__invoke($services);
        $this->assertInstanceOf(TestAsset\Router::class, $router);
    }

    public function testFactoryCanCreateRouterWhenOnlyHttpRouterConfigPresent(): void
    {
        $services = $this->createServiceManager([
            'router_class' => TestAsset\Router::class,
        ]);

        $router = $this->factory->__invoke($services);
        $this->assertInstanceOf(TestAsset\Router::class, $router);
    }

    public function testDefaultConfig(): void
    {
        $services = $this->createServiceManager();

        $router = $this->factory->__invoke($services);
        $this->assertInstanceOf(TreeRouteStack::class, $router);
    }
}
