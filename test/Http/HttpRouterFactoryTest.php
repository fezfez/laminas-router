<?php

declare(strict_types=1);

namespace LaminasTest\Router\Http;

use Laminas\Router\Http\HttpRouterFactory;
use Laminas\Router\Http\TranslatorAwareTreeRouteStack;
use Laminas\Router\Http\TreeRouteStack;
use Laminas\Router\RoutePluginManager;
use Laminas\Router\RouteStackInterface;
use Laminas\Router\RouterFactory;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Translator\TranslatorInterface;
use LaminasTest\Router\RouterFactoryTest as TestCase;
use Psr\Container\ContainerInterface;

use function array_merge_recursive;

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
                TreeRouteStack::class => \Laminas\Router\Http\TreeRouteStackFactory::class,
                TranslatorAwareTreeRouteStack::class => \Laminas\Router\Http\TranslatorAwareTreeRouteStackFactory::class,
                RouteStackInterface::class => RouterFactory::class,
                \Laminas\Router\Http\TreeRouteStackFactory::class => InvokableFactory::class,
                \Laminas\Router\Http\TranslatorAwareTreeRouteStackFactory::class => InvokableFactory::class,
                \LaminasTest\Router\TestAsset\Router::class => InvokableFactory::class,
                // @phpcs:disable Generic.Files.LineLength.TooLong
                RoutePluginManager::class => static fn(ContainerInterface $services): RoutePluginManager => new RoutePluginManager($services),
            ],
        ];

        $this->factory = new HttpRouterFactory();
    }

    public function testFactoryCanCreateTranslatorAwareRouter(): void
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $config     = array_merge_recursive($this->defaultServiceConfig, [
            'services' => [
                'config'                   => [
                    'router' => [
                        'router_class' => TranslatorAwareTreeRouteStack::class,
                        'translator'   => TranslatorInterface::class,
                    ],
                ],
                TranslatorInterface::class => $translator,
            ],
        ]);
        $services   = new ServiceManager($config);

        $router = $this->factory->__invoke($services, 'router');

        $this->assertInstanceOf(TranslatorAwareTreeRouteStack::class, $router);
        $this->assertSame($translator, $router->getTranslator());
    }
}
