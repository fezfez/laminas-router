<?php

declare(strict_types=1);

namespace Laminas\Router;

use Laminas\Router\Http\ChainBuilder;
use Laminas\Router\Http\ChainBuilderFactory;
use Laminas\Router\Http\HostnameBuilder;
use Laminas\Router\Http\HostnameBuilderFactory;
use Laminas\Router\Http\LiteralBuilder;
use Laminas\Router\Http\LiteralBuilderFactory;
use Laminas\Router\Http\MethodBuilder;
use Laminas\Router\Http\MethodBuilderFactory;
use Laminas\Router\Http\PartBuilder;
use Laminas\Router\Http\PartBuilderFactory;
use Laminas\Router\Http\PlaceholderBuilder;
use Laminas\Router\Http\PlaceholderBuilderFactory;
use Laminas\Router\Http\RegexBuilder;
use Laminas\Router\Http\RegexBuilderFactory;
use Laminas\Router\Http\SchemeBuilder;
use Laminas\Router\Http\SchemeBuilderFactory;
use Laminas\Router\Http\SegmentBuilder;
use Laminas\Router\Http\SegmentBuilderFactory;
use Laminas\Router\Http\TranslatorAwareTreeRouteStackBuilder;
use Laminas\Router\Http\TranslatorAwareTreeRouteStackBuilderFactory;
use Laminas\Router\Http\TreeRouteStack;
use Laminas\Router\Http\TreeRouteStackBuilder;
use Laminas\Router\Http\TreeRouteStackBuilderFactory;

/**
 * Provide base configuration for using the component.
 *
 * Provides base configuration expected in order to:
 *
 * - seed and configure the default routers and route builder registry.
 * - provide routes to the given routers.
 *
 * @psalm-type DependencyConfig = array{
 *     factories: array<string, class-string>
 * }
 * @psalm-type RouterConfigShape = array{
 *      dependencies: DependencyConfig,
 *      router: array{
 *          router_class: class-string<RouteStackInterface>,
 *          route_builders: array<string, class-string>,
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
                'router_class'   => TreeRouteStack::class,
                'route_builders' => RouteBuilderRegistry::defaultBuilderMap(),
            ],
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    /**
     * Provide default container dependency configuration.
     *
     * @return DependencyConfig
     */
    public function getDependencyConfig(): array
    {
        return [
            'factories' => [
                RouteBuilderRegistry::class    => RouteBuilderRegistryFactory::class,
                LiteralBuilder::class          => LiteralBuilderFactory::class,
                SegmentBuilder::class          => SegmentBuilderFactory::class,
                HostnameBuilder::class         => HostnameBuilderFactory::class,
                RegexBuilder::class            => RegexBuilderFactory::class,
                MethodBuilder::class           => MethodBuilderFactory::class,
                SchemeBuilder::class           => SchemeBuilderFactory::class,
                PlaceholderBuilder::class      => PlaceholderBuilderFactory::class,
                PartBuilder::class             => PartBuilderFactory::class,
                ChainBuilder::class            => ChainBuilderFactory::class,
                SimpleRouteStackBuilder::class => SimpleRouteStackBuilderFactory::class,
                TreeRouteStackBuilder::class   => TreeRouteStackBuilderFactory::class,
                TranslatorAwareTreeRouteStackBuilder::class
                    => TranslatorAwareTreeRouteStackBuilderFactory::class,
                TreeRouteStack::class      => RouteStackServiceFactory::class,
                RouteStackInterface::class => RouterFactory::class,
            ],
        ];
    }
}
