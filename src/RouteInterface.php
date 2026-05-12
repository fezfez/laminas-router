<?php

declare(strict_types=1);

namespace Laminas\Router;

use Laminas\Stdlib\RequestInterface;

/**
 * RouteInterface interface.
 *
 * @property int|null $priority Priority used when registering a route in a stack (optional; not enforced by PHP).
 */
interface RouteInterface
{
    /**
     * Create a new route with given options.
     */
    public static function factory(array $options = []): self;

    /**
     * Match a given request.
     */
    public function match(RequestInterface $request): RouteMatch|null;

    /**
     * Assemble the route.
     *
     * @param array<non-empty-string, string|null|int|float> $params
     */
    public function assemble(array $params = [], array $options = []): string;
}
