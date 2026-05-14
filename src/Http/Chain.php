<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use ArrayObject;
use Laminas\Router\Exception;
use Laminas\Router\PriorityList;
use Laminas\Router\RouteInterface;
use Laminas\Router\RoutePluginManager;
use Laminas\Router\RouteStackInterface;
use Laminas\Stdlib\RequestInterface;
use Laminas\Uri\Http;
use Override;

use function array_diff_key;
use function array_flip;
use function array_key_last;
use function array_reverse;
use function assert;
use function is_array;
use function is_bool;
use function method_exists;
use function strlen;

/**
 * @template TRoute of HttpRouteInterface
 * @template-implements RouteStackInterface<TRoute>
 */
final class Chain implements HttpRouteInterface, RouteStackInterface
{
    /**
     * @internal
     * @deprecated Since 3.9.0 This property will be removed or made private in version 4.0
     */
    public int|null $priority = null;

    private readonly TreeRouteStack $stack;

    /**
     * Chain routes.
     *
     * @var array<array-key, array|TRoute>
     */
    private array|null $chainRoutes;

    /**
     * List of assembled parameters.
     *
     * @var list<non-empty-string>
     */
    private array $assembledParams = [];

    /**
     * Create a new part route.
     *
     * @param array<array-key, array|TRoute> $routes
     * @param ArrayObject<string, TRoute> $prototypes
     * @param array<non-empty-string, non-empty-string> $defaultParams
     */
    public function __construct(
        RoutePluginManager $routePluginManager,
        ArrayObject $prototypes,
        array $routes = [],
        array $defaultParams = []
    ) {
        $this->chainRoutes = array_reverse($routes);
        $this->stack       = new TreeRouteStack($routePluginManager, $prototypes, [], $defaultParams);
    }

    /**
     * @inheritDoc
     * @throws Exception\InvalidArgumentException
     */
    #[Override]
    public static function factory(array $options = []): self
    {
        $route = $options['routes'] ?? null;
        /** @var ArrayObject<string, TRoute> $prototypes */
        $prototypes   = $options['prototypes'] ?? new ArrayObject();
        $routePlugins = $options['route_plugins'] ?? null;

        if ($route === null) {
            throw new Exception\InvalidArgumentException('Missing "routes" in options array');
        }

        if (! $routePlugins instanceof RoutePluginManager) {
            throw new Exception\InvalidArgumentException('Missing "route_plugins" in options array');
        }

        assert(is_array($route));

        /** @psalm-var RoutePluginManager $routePlugins */
        /** @psalm-var array<non-empty-string, TRoute> $route */

        return new self(
            $routePlugins,
            $prototypes,
            $route,
        );
    }

    /** @inheritDoc */
    public function addRoutes(array $routes): void
    {
        $this->stack->addRoutes($routes);
    }

    /** @inheritDoc */
    public function addRoute(string|int $name, int|string|array|RouteInterface $route, ?int $priority = null): void
    {
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
    public function getRoute(string $name): HttpRouteInterface|null
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

    public function setBaseUrl(string $baseUrl): void
    {
        $this->stack->setBaseUrl($baseUrl);
    }

    public function getBaseUrl(): ?string
    {
        return $this->stack->getBaseUrl();
    }

    public function setRequestUri(Http $uri): void
    {
        $this->stack->setRequestUri($uri);
    }

    public function getRequestUri(): ?Http
    {
        return $this->stack->getRequestUri();
    }

    /** @inheritDoc */
    public function match(
        RequestInterface $request,
        int|null $pathOffset = null,
        array $options = []
    ): ?HttpRouteMatch {
        if (! method_exists($request, 'getUri')) {
            return null;
        }

        $mustTerminate = $pathOffset === null;
        $pathOffset  ??= 0;

        if ($this->chainRoutes !== null) {
            $this->stack->addRoutes($this->chainRoutes);
            $this->chainRoutes = null;
        }

        $match = new HttpRouteMatch([]);
        /** @var Http $uri */
        $uri        = $request->getUri();
        $pathLength = strlen((string) $uri->getPath());

        foreach ($this->stack->getRoutes() as $route) {
            assert($route instanceof HttpRouteInterface);
            $subMatch = $route->match($request, $pathOffset, $options);

            if ($subMatch === null) {
                return null;
            }

            assert($subMatch instanceof HttpRouteMatch);

            $match->merge($subMatch);
            $pathOffset += $subMatch->getLength();
        }

        if ($mustTerminate && $pathOffset !== $pathLength) {
            return null;
        }

        return $match;
    }

    /** @inheritDoc */
    #[Override]
    public function assemble(array $params = [], array $options = []): string
    {
        if ($this->chainRoutes !== null) {
            $this->stack->addRoutes($this->chainRoutes);
            $this->chainRoutes = null;
        }

        $this->assembledParams = [];

        $routes       = [...$this->stack->getRoutes()];
        $lastRouteKey = array_key_last($routes);
        $path         = '';

        foreach ($routes as $key => $route) {
            assert($route instanceof HttpRouteInterface);
            $chainOptions = $options;
            $hasChild     = isset($options['has_child']) && is_bool($options['has_child']) && $options['has_child'];

            $chainOptions['has_child'] = $hasChild || $key !== $lastRouteKey;

            $path  .= $route->assemble($params, $chainOptions);
            $params = array_diff_key($params, array_flip($route->getAssembledParams()));

            $this->assembledParams = [
                ...$this->assembledParams,
                ...$route->getAssembledParams(),
            ];
        }

        return $path;
    }

    /** @inheritDoc */
    #[Override]
    public function getAssembledParams(): array
    {
        return $this->assembledParams;
    }
}
