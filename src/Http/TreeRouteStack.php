<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use ArrayObject;
use Laminas\Router\Exception;
use Laminas\Router\Exception\RuntimeException;
use Laminas\Router\RouteInterface;
use Laminas\Router\RouteMatch;
use Laminas\Router\RoutePluginManager;
use Laminas\Router\SimpleRouteStack;
use Laminas\Stdlib\RequestInterface;
use Laminas\Uri\Http as HttpUri;
use Override;

use function array_merge;
use function assert;
use function explode;
use function is_array;
use function is_string;
use function method_exists;
use function property_exists;
use function rtrim;
use function sprintf;
use function strlen;

/**
 * Tree search implementation.
 *
 * @template TRoute of HttpRouteInterface
 * @template-extends SimpleRouteStack<TRoute>
 */
class TreeRouteStack extends SimpleRouteStack
{
    /**
     * Base URL.
     */
    private string|null $baseUrl = null;

    /**
     * Request URI.
     */
    private HttpUri|null $requestUri = null;

    /**
     * @internal
     * @deprecated Since 3.9.0 This property will be removed or made private in version 4.0
     */
    public int|null $priority = null;

    /**
     * @param ArrayObject<string, TRoute> $prototypes
     * @param array<non-empty-string, array|TRoute> $routes
     * @param array<non-empty-string, non-empty-string> $defaultParams
     */
    public function __construct(
        private readonly RoutePluginManager $routePluginManager,
        /**
         * Prototype routes.
         *
         * We use an ArrayObject in this case so we can easily pass it down the tree
         * by reference.
         */
        private readonly ArrayObject $prototypes,
        array $routes = [],
        array $defaultParams = []
    ) {
        parent::__construct($this->routePluginManager, $routes, $defaultParams);
    }

    /**
     * @inheritDoc
     * @throws Exception\InvalidArgumentException
     */
    #[Override]
    public static function factory(array $options = []): static
    {
        /** @psalm-var array<non-empty-string, array|TRoute>  $routes */
        $routes = $options['routes'] ?? [];
        /** @var ArrayObject<string, TRoute> $prototypes */
        $prototypes   = $options['prototypes'] ?? new ArrayObject();
        $routePlugins = $options['route_plugins'] ?? null;
        /** @psalm-var array<non-empty-string, non-empty-string> $defaultParams */
        $defaultParams = $options['default_params'] ?? [];

        if (! $routePlugins instanceof RoutePluginManager) {
            throw new RuntimeException('Missing "route_plugins" in options array');
        }

        return new static(
            $routePlugins,
            $prototypes,
            $routes,
            $defaultParams
        );
    }

    /** @inheritDoc */
    #[Override]
    public function addRoute(string|int $name, int|string|array|RouteInterface $route, ?int $priority = null): void
    {
        if (! $route instanceof HttpRouteInterface && $route instanceof RouteInterface) {
            throw new Exception\InvalidArgumentException('Route definition must be an array or Traversable object');
        }
        if (! $route instanceof HttpRouteInterface) {
            $route = $this->routeFromArray($route);
        }

        parent::addRoute($name, $route, $priority);
    }

    /**
     * @inheritDoc
     * @param  string|array $specs
     * @return TRoute
     * @throws Exception\InvalidArgumentException When route definition is not an array nor traversable.
     * @throws Exception\InvalidArgumentException When chain routes are not an array nor traversable.
     * @throws Exception\RuntimeException         When a generated routes does not implement the HTTP route interface.
     */
    #[Override]
    final protected function routeFromArray(string|array $specs): RouteInterface
    {
        if (is_string($specs)) {
            return $this->getPrototype($specs);
        }

        if (isset($specs['chain_routes'])) {
            if (! is_array($specs['chain_routes'])) {
                throw new Exception\InvalidArgumentException('Chain routes must be an array');
            }

            $chainRoutes = array_merge([$specs], $specs['chain_routes']);
            if (isset($chainRoutes[0]['chain_routes'])) {
                unset($chainRoutes[0]['chain_routes']);
            }

            if (isset($specs['child_routes']) && isset($chainRoutes[0]['child_routes'])) {
                unset($chainRoutes[0]['child_routes']);
            }

            $options = [
                'routes'        => $chainRoutes,
                'route_plugins' => $this->routePluginManager,
                'prototypes'    => $this->prototypes,
            ];

            $route = $this->routePluginManager->build(Chain::class, $options);
        } else {
            $route = parent::routeFromArray($specs);
        }

        if (! $route instanceof HttpRouteInterface) {
            throw new Exception\RuntimeException('Given route does not implement HTTP route interface');
        }

        if (isset($specs['child_routes'])) {
            $options = [
                'route'         => $route,
                'may_terminate' => isset($specs['may_terminate']) && $specs['may_terminate'] === true,
                'child_routes'  => $specs['child_routes'],
                'route_plugins' => $this->routePluginManager,
                'prototypes'    => $this->prototypes,
            ];

            $priority = $route->priority ?? null;

            $route           = $this->routePluginManager->build(Part::class, $options);
            $route->priority = $priority;
        }

        return $route;
    }

    /**
     * Get a prototype.
     *
     * @return TRoute
     */
    private function getPrototype(string $name): RouteInterface
    {
        if (! property_exists($this->prototypes, $name)) {
            throw new Exception\RuntimeException(sprintf('Could not find prototype with name %s', $name));
        }
        return $this->prototypes[$name];
    }

    /**
     * @inheritDoc
     * @param int|null $pathOffset
     */
    #[Override]
    public function match(
        RequestInterface $request,
        int|null $pathOffset = null,
        array $options = []
    ): ?RouteMatch {
        if (! method_exists($request, 'getUri')) {
            return null;
        }

        if ($this->baseUrl === null && method_exists($request, 'getBaseUrl')) {
            $this->setBaseUrl((string) $request->getBaseUrl());
        }

        /** @var HttpUri $uri */
        $uri           = $request->getUri();
        $baseUrlLength = strlen((string) $this->baseUrl) ?: null;

        if ($pathOffset !== null) {
            $baseUrlLength = $baseUrlLength !== null ? $baseUrlLength + $pathOffset : $pathOffset;
        }

        if ($this->requestUri === null) {
            $this->setRequestUri($uri);
        }

        $pathLength = null;
        if ($baseUrlLength !== null) {
            $pathLength = strlen((string) $uri->getPath()) - $baseUrlLength;
        }

        foreach ($this->routes as $name => $route) {
            assert($route instanceof HttpRouteInterface);
            $match = $route->match($request, $baseUrlLength, $options);
            if ($match instanceof HttpRouteMatch && ($pathLength === null || $match->getLength() === $pathLength)) {
                $match->setMatchedRouteName((string) $name);

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
    public function assemble(array $params = [], array $options = []): string
    {
        $name = $options['name'] ?? '';
        if (! is_string($name) || $name === '') {
            throw new Exception\InvalidArgumentException('Missing "name" option');
        }

        $names = explode('/', $name, 2);

        if ($names[0] === '') {
            throw new Exception\RuntimeException('Invalid route name');
        }

        $route = $this->routes->get($names[0]);

        if (! $route) {
            throw new Exception\RuntimeException(sprintf('Route with name "%s" not found', $names[0]));
        }

        if (isset($names[1])) {
            if (! $route instanceof TreeRouteStack) {
                throw new Exception\RuntimeException(sprintf(
                    'Route with name "%s" does not have child routes',
                    $names[0]
                ));
            }
            $options['name'] = $names[1];
        } else {
            unset($options['name']);
        }

        if (isset($options['only_return_path']) && $options['only_return_path'] === true) {
            return ($this->baseUrl ?? '') . $route->assemble(array_merge($this->defaultParams, $params), $options);
        }

        if (! isset($options['uri']) || ! $options['uri'] instanceof HttpUri) {
            $uri = new HttpUri();

            if (isset($options['force_canonical']) && $options['force_canonical'] === true) {
                if ($this->requestUri === null) {
                    throw new Exception\RuntimeException('Request URI has not been set');
                }

                $uri->setScheme($this->requestUri->getScheme())
                    ->setHost($this->requestUri->getHost())
                    ->setPort($this->requestUri->getPort());
            }

            $options['uri'] = $uri;
        } else {
            $uri = $options['uri'];
        }

        $path = ($this->baseUrl ?? '') . $route->assemble(array_merge($this->defaultParams, $params), $options);

        if (isset($options['query']) && (is_string($options['query']) || is_array($options['query']))) {
            $uri->setQuery($options['query']);
        }

        if (isset($options['fragment']) && is_string($options['fragment'])) {
            $uri->setFragment($options['fragment']);
        }

        if (
            (isset($options['force_canonical'])
            && $options['force_canonical'] === true)
            || $uri->getHost() !== null
            || $uri->getScheme() !== null
        ) {
            if (($uri->getHost() === null || $uri->getScheme() === null) && $this->requestUri === null) {
                throw new Exception\RuntimeException('Request URI has not been set');
            }

            if ($uri->getHost() === null && $this->requestUri !== null) {
                $uri->setHost($this->requestUri->getHost());
            }

            if ($uri->getScheme() === null && $this->requestUri !== null) {
                $uri->setScheme($this->requestUri->getScheme());
            }

            $uri->setPath($path);

            if (! isset($options['normalize_path']) || $options['normalize_path'] === true) {
                $uri->normalize();
            }

            return $uri->toString();
        } elseif (! $uri->isAbsolute() && $uri->isValidRelative()) {
            $uri->setPath($path);

            if (! isset($options['normalize_path']) || $options['normalize_path'] === true) {
                $uri->normalize();
            }

            return $uri->toString();
        }

        return $path;
    }

    /**
     * Set the base URL.
     */
    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * Get the base URL.
     */
    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    /**
     * Set the request URI.
     */
    public function setRequestUri(HttpUri $uri): void
    {
        $this->requestUri = $uri;
    }

    /**
     * Get the request URI.
     */
    public function getRequestUri(): ?HttpUri
    {
        return $this->requestUri;
    }
}
