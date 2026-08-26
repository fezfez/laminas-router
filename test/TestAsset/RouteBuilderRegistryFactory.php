<?php

declare(strict_types=1);

namespace LaminasTest\Router\TestAsset;

use Laminas\Router\Http\ChainBuilder;
use Laminas\Router\Http\HostnameBuilder;
use Laminas\Router\Http\LiteralBuilder;
use Laminas\Router\Http\MethodBuilder;
use Laminas\Router\Http\PartBuilder;
use Laminas\Router\Http\PlaceholderBuilder;
use Laminas\Router\Http\RegexBuilder;
use Laminas\Router\Http\SchemeBuilder;
use Laminas\Router\Http\SegmentBuilder;
use Laminas\Router\Http\TranslatorAwareTreeRouteStackBuilder;
use Laminas\Router\Http\TreeRouteStackBuilder;
use Laminas\Router\RouteBuilderInterface;
use Laminas\Router\RouteBuilderRegistry;
use Laminas\Router\SimpleRouteStackBuilder;

/**
 * Assembles a RouteBuilderRegistry with built-in builders for tests.
 */
final class RouteBuilderRegistryFactory
{
    /**
     * @param array<string, RouteBuilderInterface> $extraBuilders
     */
    public static function withDefaults(array $extraBuilders = []): RouteBuilderRegistry
    {
        $container = new InMemoryContainer();
        $registry  = new RouteBuilderRegistry(
            $container,
            RouteBuilderRegistry::defaultBuilderMap(),
            $extraBuilders
        );

        $container->set(LiteralBuilder::class, new LiteralBuilder());
        $container->set(SegmentBuilder::class, new SegmentBuilder());
        $container->set(HostnameBuilder::class, new HostnameBuilder());
        $container->set(RegexBuilder::class, new RegexBuilder());
        $container->set(MethodBuilder::class, new MethodBuilder());
        $container->set(SchemeBuilder::class, new SchemeBuilder());
        $container->set(PlaceholderBuilder::class, new PlaceholderBuilder());

        $container->set(SimpleRouteStackBuilder::class, new SimpleRouteStackBuilder($registry));
        $container->set(TreeRouteStackBuilder::class, new TreeRouteStackBuilder($registry));
        $container->set(PartBuilder::class, new PartBuilder($registry));
        $container->set(ChainBuilder::class, new ChainBuilder($registry));
        $container->set(
            TranslatorAwareTreeRouteStackBuilder::class,
            new TranslatorAwareTreeRouteStackBuilder($registry, new DummyTranslator())
        );

        return $registry;
    }
}
