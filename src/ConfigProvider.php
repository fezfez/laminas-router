<?php

declare(strict_types=1);

namespace Laminas\Router;

use Laminas\Router\Http\TreeRouteStack;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Translator\TranslatorInterface;

/**
 * Provide base configuration for using the component.
 *
 * Provides base configuration expected in order to:
 *
 * - seed and configure the default routers and route plugin manager.
 * - provide routes to the given routers.
 *
 * @see ConfigInterface
 *
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 * @psalm-type RouterConfigShape = array{
 *      dependencies: ServiceManagerConfiguration,
 *      router: array{
 *          router_class: class-string<RouteStackInterface>,
 *          route_plugins: class-string<RoutePluginManager>,
 *          translator?: class-string<TranslatorInterface>,
 *      }
 *  }
 */
final readonly class ConfigProvider
{
    /**
     * Provide default configuration.
     *
     * @return RouterConfigShape
     */
    public function __invoke(): array
    {
        return [
            'router'       => [
                'router_class'  => TreeRouteStack::class,
                'route_plugins' => RoutePluginManager::class,
            ],
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    /**
     * Provide default container dependency configuration.
     *
     * @return ServiceManagerConfiguration
     */
    public function getDependencyConfig(): array
    {
        return [
            'factories' => [
                TreeRouteStack::class                            => Http\TreeRouteStackFactory::class,
                Http\ChainFactory::class                         => InvokableFactory::class,
                Http\HostnameFactory::class                      => InvokableFactory::class,
                Http\LiteralFactory::class                       => InvokableFactory::class,
                Http\MethodFactory::class                        => InvokableFactory::class,
                Http\PartFactory::class                          => InvokableFactory::class,
                Http\PlaceholderFactory::class                   => InvokableFactory::class,
                Http\RegexFactory::class                         => InvokableFactory::class,
                Http\SchemeFactory::class                        => InvokableFactory::class,
                Http\SegmentFactory::class                       => InvokableFactory::class,
                Http\TreeRouteStackFactory::class                => InvokableFactory::class,
                Http\TranslatorAwareTreeRouteStackFactory::class => InvokableFactory::class,
                Http\TranslatorAwareTreeRouteStack::class       => Http\TranslatorAwareTreeRouteStackFactory::class,
                SimpleRouteStack::class                          => SimpleRouteStackFactory::class,
                SimpleRouteStackFactory::class                   => InvokableFactory::class,
                RoutePluginManager::class                        => RoutePluginManagerFactory::class,
                RouteStackInterface::class                       => RouterFactory::class,
            ],
        ];
    }
}
