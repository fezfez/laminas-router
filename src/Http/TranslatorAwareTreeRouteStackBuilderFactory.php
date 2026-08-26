<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\RouteBuilderRegistry;
use Laminas\Translator\TranslatorInterface;
use Psr\Container\ContainerInterface;

use function assert;

/**
 * @internal
 *
 * @psalm-internal \Laminas\Router
 * @psalm-internal \LaminasTest\Router
 */
final readonly class TranslatorAwareTreeRouteStackBuilderFactory
{
    public function __invoke(
        ContainerInterface $container
    ): TranslatorAwareTreeRouteStackBuilder {
        $registry = $container->get(RouteBuilderRegistry::class);
        assert($registry instanceof RouteBuilderRegistry);
        $translator = $container->get(TranslatorInterface::class);
        assert($translator instanceof TranslatorInterface);

        return new TranslatorAwareTreeRouteStackBuilder($registry, $translator);
    }
}
