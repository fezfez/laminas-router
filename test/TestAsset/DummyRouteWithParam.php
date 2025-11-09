<?php

declare(strict_types=1);

namespace LaminasTest\Router\TestAsset;

use Laminas\Router\RouteMatch;
use Laminas\Stdlib\RequestInterface;

/**
 * Dummy route.
 */
final class DummyRouteWithParam extends DummyRoute
{
    /**
     * match(): defined by RouteInterface interface.
     *
     * @see    Route::match()
     *
     * @return RouteMatch
     */
    public function match(RequestInterface $request)
    {
        return new RouteMatch(['foo' => 'bar']);
    }

    /**
     * assemble(): defined by RouteInterface interface.
     *
     * @see    Route::assemble()
     *
     * @return mixed
     */
    public function assemble(?array $params = null, ?array $options = null)
    {
        return $params['foo'] ?? '';
    }
}
