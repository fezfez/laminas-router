<?php

declare(strict_types=1);

namespace Laminas\Router;

use Laminas\Stdlib\RequestInterface;

/**
 * RouteInterface interface.
 */
interface RouteInterface
{
    /**
     * Priority used for route stacks.
     *
     * @var int
     * public int|null $priority = null;
     */

    /**
     * Create a new route with given options.
     */
    public static function factory(iterable $options = []): static;

    /**
     * Match a given request.
     */
    public function match(RequestInterface $request): RouteMatch|null;

    /**
     * Assemble the route.
     */
    public function assemble(array $params = [], array $options = []): mixed;
}
