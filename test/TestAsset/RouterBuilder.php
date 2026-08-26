<?php

declare(strict_types=1);

namespace LaminasTest\Router\TestAsset;

use Laminas\Router\RouteBuilderInterface;
use Laminas\Router\RouteInterface;

/**
 * @implements RouteBuilderInterface<Router>
 */
final readonly class RouterBuilder implements RouteBuilderInterface
{
    public function build(array $options = []): RouteInterface
    {
        return new Router();
    }
}
