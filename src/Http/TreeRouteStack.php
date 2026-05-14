<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use ArrayObject;
use Laminas\Router\Exception;
use Laminas\Router\Exception\RuntimeException;
use Laminas\Router\PriorityList;
use Laminas\Router\RouteInterface;
use Laminas\Router\RouteMatch;
use Laminas\Router\RoutePluginManager;
use Laminas\Router\RouteStackInterface;
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
use function rtrim;
use function sprintf;
use function strlen;

/**
 * Tree search implementation.
 *
 * @template TRoute of HttpRouteInterface
 * @template-implements RouteStackInterface<TRoute>
 */
final class TreeRouteStack implements RouteStackInterface
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

    private readonly HttpRouteSpecificationFactory $routeSpecificationFactory;

    /** @var SimpleRouteStack<TRoute> */
    private SimpleRouteStack $stack;

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
        $this->stack                     = new SimpleRouteStack($this->routePluginManager, [], $defaultParams);
        $this->routeSpecificationFactory = new HttpRouteSpecificationFactory(
            $this->routePluginManager,
            $this->prototypes
        );
        $this->addRoutes($routes);
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

        return new self(
            $routePlugins,
            $prototypes,
            $routes,
            $defaultParams
        );
    }

    /** @inheritDoc */
    public function addRoutes(array $routes): void
    {
        foreach ($routes as $name => $route) {
            $this->addRoute($name, $route);
        }
    }

    /** @inheritDoc */
    #[Override]
    public function addRoute(string|int $name, int|string|array|RouteInterface $route, ?int $priority = null): void
    {
        if (! $route instanceof HttpRouteInterface && $route instanceof RouteInterface) {
            throw new Exception\InvalidArgumentException(
                'Only HttpRouteInterface instances or array/string specifications are allowed.'
            );
        }
        if (! $route instanceof HttpRouteInterface) {
            /** @var mixed $routeSpec */
            $routeSpec = $route;
            assert(is_string($routeSpec) || is_array($routeSpec));
            $route = $this->routeSpecificationFactory->createFromSpecification($routeSpec);
        }

        assert($route instanceof HttpRouteInterface);
        /** @var TRoute $route */
        $this->stack->addRoute($name, $route, $priority);
    }

    /** @inheritDoc */
    public function removeRoute(string $name): void
    {
        $this->stack->removeRoute($name);
    }

    /** @inheritDoc */
    public function setRoutes(array $routes): void
    {
        /** @var array<non-empty-string, array|TRoute> $routes */
        $this->stack->setRoutes($routes);
    }

    public function getRoutes(): PriorityList
    {
        return $this->stack->getRoutes();
    }

    /**
     * @param non-empty-string $name
     */
    public function hasRoute(string $name): bool
    {
        return $this->stack->hasRoute($name);
    }

    /**
     * @param non-empty-string $name
     * @return TRoute|null the route
     */
    public function getRoute(string $name): RouteInterface|null
    {
        return $this->stack->getRoute($name);
    }

    /**
     * @param non-empty-string $name
     * @param non-empty-string $value
     */
    public function setDefaultParam(string $name, string $value): void
    {
        $this->stack->setDefaultParam($name, $value);
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

        foreach ($this->stack->getRoutes() as $name => $route) {
            assert($route instanceof HttpRouteInterface);
            $match = $route->match($request, $baseUrlLength, $options);
            if ($match instanceof HttpRouteMatch && ($pathLength === null || $match->getLength() === $pathLength)) {
                $match->setMatchedRouteName((string) $name);

                foreach ($this->stack->getDefaultParams() as $paramName => $value) {
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

        $route = $this->stack->getRoutes()->get($names[0]);

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
            return ($this->baseUrl ?? '')
                . $route->assemble(array_merge($this->stack->getDefaultParams(), $params), $options);
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

        $path = ($this->baseUrl ?? '')
            . $route->assemble(array_merge($this->stack->getDefaultParams(), $params), $options);

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
