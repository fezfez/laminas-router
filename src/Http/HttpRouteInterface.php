<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\RouteInterface;
use Laminas\Router\RouteMatch;
use Laminas\Stdlib\RequestInterface;

/**
 * Tree specific route interface.
 *
 * @property int|null $priority Priority used when registering a route in a stack (optional; not enforced by PHP).
 */
interface HttpRouteInterface extends RouteInterface
{
    /**
     * Get a list of parameters used while assembling.
     *
     * @return list<non-empty-string>
     */
    public function getAssembledParams(): array;

    public function match(RequestInterface $request, int|null $pathOffset = null, array $options = []): RouteMatch|null;
}
