<?php

declare(strict_types=1);

namespace LaminasTest\Router\TestAsset;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

final readonly class DummyRouteFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, string $requestedName, ?array $options = null): DummyRoute
    {
        $options ??= [];
        return new DummyRoute(
            (string) ($options['name'] ?? ''),
            $options['priority'] ?? null,
            $options['defaults'] ?? [],
        );
    }
}
