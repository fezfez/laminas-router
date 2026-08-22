<?php

declare(strict_types=1);

namespace LaminasTest\Router\TestAsset;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

final readonly class DummyRouteWithParamFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, string $requestedName, ?array $options = null): DummyRouteWithParam
    {
        $options ??= [];
        return new DummyRouteWithParam(
            (string) ($options['name'] ?? ''),
            $options['priority'] ?? null,
        );
    }
}
