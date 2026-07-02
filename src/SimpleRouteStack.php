<?php

declare(strict_types=1);

namespace Laminas\Router;

use Laminas\Router\AssembledUrl;
use Laminas\Router\Exception\RuntimeException;
use Laminas\Router\PriorityList;
use Laminas\Router\RouteMatch;
use Override;
use Psr\Http\Message\RequestInterface;

use function array_merge;
use function assert;
use function is_array;
use function is_int;
use function is_string;
use function sprintf;

/**
 * Simple route stack implementation.
 *
 * @template TRoute of RouteInterface
 * @template-implements RouteStackInterface<TRoute>
 */
readonly class SimpleRouteStack implements RouteStackInterface
{
    /**
     * Stack containing all routes.
     *
     * @var PriorityList<TRoute>
     */
    protected PriorityList $routes;

    /**
     * @param array<non-empty-string|array-key, array|TRoute> $routes
     * @param array<non-empty-string, non-empty-string> $defaultParams
     */
    public function __construct(
        private RoutePluginManager $routePluginManager,
        array $routes = [],
        /**
         * Default parameters.
         */
        protected array $defaultParams = []
    ) {
        /** @var PriorityList<TRoute> $priorityList */
        $priorityList = new PriorityList();
        $this->routes = $priorityList;
        $this->addRoutes($routes);
    }

    /**
     * @inheritDoc
     * @throws Exception\InvalidArgumentException
     */
    #[Override]
    public static function factory(array $options = []): self
    {
        /** @psalm-var array<non-empty-string, array|TRoute>  $routes */
        $routes       = $options['routes'] ?? [];
        $routePlugins = $options['route_plugins'] ?? null;
        /** @psalm-var array<non-empty-string, non-empty-string> $defaultParams */
        $defaultParams = $options['default_params'] ?? [];

        if (! $routePlugins instanceof RoutePluginManager) {
            throw new RuntimeException('Missing "route_plugins" in options array');
        }
        return new self(
            $routePlugins,
            $routes,
            $defaultParams
        );
    }

    /** @inheritDoc */
    #[Override]
    public function addRoutes(array $routes): void
    {
        foreach ($routes as $name => $route) {
            $name = is_int($name) ? (string) $name : $name;
            assert($name !== '');
            $this->addRoute($name, $route);
        }
    }

    /**
     * @inheritDoc
     * @throws Exception\InvalidArgumentException
     */
    #[Override]
    public function addRoute(string $name, array|RouteInterface $route, ?int $priority = null): void
    {
        if (is_array($route)) {
            $route = $this->routeFromArray($route);
        }

        $this->routes->insert($name, $route, $priority);
    }

    /** @inheritDoc */
    #[Override]
    public function removeRoute(string $name): void
    {
        $this->routes->remove($name);
    }

    /** @inheritDoc */
    #[Override]
    public function setRoutes(array $routes): void
    {
        $this->routes->clear();
        $this->addRoutes($routes);
    }

    /**
     * Get the added routes
     */
    public function getRoutes(): PriorityList
    {
        return $this->routes;
    }

    /**
     * Check if a route with a specific name exists
     *
     * @param non-empty-string $name
     */
    public function hasRoute(string $name): bool
    {
        return $this->routes->get($name) !== null;
    }

    /**
     * Get a route by name
     *
     * @param non-empty-string $name
     * @return TRoute|null the route
     */
    public function getRoute(string $name): RouteInterface|null
    {
        return $this->routes->get($name);
    }

    /**
     * Create a route from array specifications.
     *
     * @return TRoute
     * @throws Exception\InvalidArgumentException
     */
    protected function routeFromArray(array $specs): RouteInterface
    {
        $type = $specs['type'] ?? null;
        /** @var array<string, string> $option */
        $option = $specs['options'] ?? [];

        if (! is_string($type) || $type === '') {
            throw new Exception\InvalidArgumentException('Missing "type" option');
        }

        /** @psalm-var TRoute $route */
        $route = $this->routePluginManager->build($type, [...$option, 'priority' => $specs['priority'] ?? null]);

        return $route;
    }

    /** @inheritDoc */
    #[Override]
    public function match(RequestInterface $request): ?RouteMatch
    {
        foreach ($this->routes->getAsArray() as $name => $route) {
            $match = $route->match($request);
            if ($match !== null) {
                $match = $match->setMatchedRouteName($name);
                return $match->setDefaults($this->defaultParams);
            }
        }

        return null;
    }

    #[Override]
    public function getPriority(): ?int
    {
        return null;
    }

    /**
     * @inheritDoc
     * @throws Exception\InvalidArgumentException
     * @throws RuntimeException
     */
    #[Override]
    public function assemble(array $params = [], array $options = []): AssembledUrl
    {
        $name = $options['name'] ?? null;
        if (! is_string($name) || $name === '') {
            throw new Exception\InvalidArgumentException('Missing "name" option');
        }

        $route = $this->routes->get($name);

        if (! $route instanceof RouteInterface) {
            throw new RuntimeException(sprintf('Route with name "%s" not found', $name));
        }

        unset($options['name']);

        return $route->assemble(array_merge($this->defaultParams, $params), $options);
    }
}
