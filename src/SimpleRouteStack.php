<?php

declare(strict_types=1);

namespace Laminas\Router;

use Laminas\Router\PriorityList;
use Laminas\Router\RouteMatch;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Stdlib\RequestInterface;
use Override;
use Traversable;

use function array_merge;
use function sprintf;

/**
 * Simple route stack implementation.
 *
 * @template TRoute of RouteInterface
 * @template-implements RouteStackInterface<TRoute>
 */
class SimpleRouteStack implements RouteStackInterface
{
    /**
     * Stack containing all routes.
     *
     * @var PriorityList<string, TRoute>
     */
    protected PriorityList $routes;

    /**
     * Default parameters.
     */
    protected array $defaultParams = [];

    protected RoutePluginManager $routePluginManager;

    public function __construct(
        ?RoutePluginManager $routePluginManager = null
    ) {
        /** @var PriorityList<string, TRoute> $this->routes */
        $this->routes             = new PriorityList();
        $this->routePluginManager = $routePluginManager ?? new RoutePluginManager(new ServiceManager());
    }

    /**
     * @inheritDoc
     * @throws Exception\InvalidArgumentException
     */
    #[Override]
    public static function factory(iterable $options = []): static
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        $routePluginManager = null;
        if (isset($options['route_plugins'])) {
            $routePluginManager = $options['route_plugins'];
        }

        $instance = new static($routePluginManager);

        if (isset($options['routes'])) {
            $instance->addRoutes($options['routes']);
        }

        if (isset($options['default_params'])) {
            $instance->setDefaultParams($options['default_params']);
        }

        return $instance;
    }

    /** @inheritDoc */
    #[Override]
    public function addRoutes(iterable $routes): static
    {
        foreach ($routes as $name => $route) {
            $this->addRoute($name, $route);
        }

        return $this;
    }

    /** @inheritDoc */
    #[Override]
    public function addRoute(string|int $name, iterable|RouteInterface $route, ?int $priority = null): static
    {
        if (! $route instanceof RouteInterface) {
            $route = $this->routeFromArray($route);
        }

        if ($priority === null && isset($route->priority)) {
            $priority = $route->priority;
        }

        $this->routes->insert($name, $route, $priority);

        return $this;
    }

    /** @inheritDoc */
    #[Override]
    public function removeRoute(string $name): static
    {
        $this->routes->remove($name);
        return $this;
    }

    /** @inheritDoc */
    #[Override]
    public function setRoutes(iterable $routes): static
    {
        $this->routes->clear();
        $this->addRoutes($routes);
        return $this;
    }

    /**
     * Get the added routes
     */
    public function getRoutes(): Traversable
    {
        return $this->routes;
    }

    /**
     * Check if a route with a specific name exists
     */
    public function hasRoute(string $name): bool
    {
        return $this->routes->get($name) !== null;
    }

    /**
     * Get a route by name
     *
     * @return TRoute|null the route
     */
    public function getRoute(string $name): RouteInterface|null
    {
        return $this->routes->get($name);
    }

    /**
     * Set a default parameters.
     */
    public function setDefaultParams(array $params): static
    {
        $this->defaultParams = $params;
        return $this;
    }

    /**
     * Set a default parameter.
     */
    public function setDefaultParam(string $name, mixed $value): static
    {
        $this->defaultParams[$name] = $value;
        return $this;
    }

    /**
     * Create a route from array specifications.
     *
     * @return TRoute
     * @throws Exception\InvalidArgumentException
     */
    protected function routeFromArray(iterable $specs): RouteInterface
    {
        if ($specs instanceof Traversable) {
            $specs = ArrayUtils::iteratorToArray($specs);
        }

        if (! isset($specs['type'])) {
            throw new Exception\InvalidArgumentException('Missing "type" option');
        }

        if (! isset($specs['options'])) {
            $specs['options'] = [];
        }

        $route = $this->routePluginManager->build($specs['type'], $specs['options']);

        if (isset($specs['priority'])) {
            $route->priority = $specs['priority'];
        }

        return $route;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function match(RequestInterface $request): ?RouteMatch
    {
        foreach ($this->routes as $name => $route) {
            $match = $route->match($request);
            if ($match instanceof RouteMatch) {
                $match->setMatchedRouteName($name);

                foreach ($this->defaultParams as $paramName => $value) {
                    if ($match->getParam($paramName) === null) {
                        $match->setParam($paramName, $value);
                    }
                }

                return $match;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     */
    #[Override]
    public function assemble(array $params = [], array $options = []): mixed
    {
        if (! isset($options['name'])) {
            throw new Exception\InvalidArgumentException('Missing "name" option');
        }

        $route = $this->routes->get($options['name']);

        if (! $route) {
            throw new Exception\RuntimeException(sprintf('Route with name "%s" not found', $options['name']));
        }

        unset($options['name']);

        return $route->assemble(array_merge($this->defaultParams, $params), $options);
    }
}
