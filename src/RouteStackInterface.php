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
     * @param iterable|TRoute $route
     */
    public function addRoute(string $name, iterable|RouteInterface $route, ?int $priority = null): static;

    /**
     * Add multiple routes to the stack.
     */
    public function addRoutes(iterable $routes): static;

    /**
     * Remove a route from the stack.
     */
    public function removeRoute(string $name): static;

    /**
     * Remove all routes from the stack and set new ones.
     */
    public function setRoutes(iterable $routes): static;
}
