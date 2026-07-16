<?php

declare(strict_types=1);

namespace LaminasTest\Router;

use Laminas\Router\RoutePluginManager;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;

final class RoutePluginManagerTest extends TestCase
{
    public function testLoadNonExistentRoute(): void
    {
        $routes = new RoutePluginManager(new ServiceManager());
        $this->expectException(ServiceNotFoundException::class);
        $routes->build('foo', ['name' => 'foo']);
    }

    public function testCanLoadAnyRoute(): void
    {
        $routes = new RoutePluginManager(new ServiceManager(), [
            'invokables' => [
                'DummyRoute' => TestAsset\DummyRoute::class,
            ],
        ]);
        $route  = $routes->build('DummyRoute', ['name' => 'foo']);

        $this->assertInstanceOf(TestAsset\DummyRoute::class, $route);
    }
}
