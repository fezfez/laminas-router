<?php

declare(strict_types=1);

namespace Laminas\Router;

/**
 * @template TRoute of RouteInterface
 */
interface RouteStackInterface extends RouteInterface
{
    /**
     * Add a route to the stack.
     *
     * @param non-empty-string $name
     * @param array|TRoute $route
     * @throws Exception\InvalidArgumentException
     */
    public function addRoute(string $name, array|RouteInterface $route, ?int $priority = null): void;

    /**
     * Add multiple routes to the stack.
     *
     * @param array<non-empty-string|array-key, array|TRoute> $routes
     */
    public function addRoutes(array $routes): void;

    /**
     * Remove a route from the stack.
     *
     * @param non-empty-string $name
     */
    public function removeRoute(string $name): void;

    /**
     * Remove all routes from the stack and set new ones.
     *
     * @param array<non-empty-string, array|TRoute> $routes
     */
    public function setRoutes(array $routes): void;
}
