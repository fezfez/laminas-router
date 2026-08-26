<?php

declare(strict_types=1);

namespace LaminasTest\Router\Http\TestAsset;

use Laminas\Router\RouteBuilderInterface;
use Laminas\Router\RouteInterface;

use function is_string;

/**
 * @implements RouteBuilderInterface<DummyRouteWithParam>
 */
final readonly class DummyRouteWithParamBuilder implements RouteBuilderInterface
{
    public function build(array $options = []): RouteInterface
    {
        /** @psalm-var mixed $nameOption */
        $nameOption = $options['name'] ?? 'dummy';
        $name       = is_string($nameOption) ? $nameOption : 'dummy';

        return new DummyRouteWithParam($name);
    }
}
