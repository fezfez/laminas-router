<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\RouteInterface;
use Laminas\Router\RouteMatch;
use Psr\Http\Message\RequestInterface;

/**
 * Tree specific route interface.
 */
interface HttpRouteInterface extends RouteInterface
{
    public function match(RequestInterface $request, int|null $pathOffset = null, array $options = []): RouteMatch|null;
}
