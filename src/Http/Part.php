<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use ArrayObject;
use Laminas\Router\Exception;
use Laminas\Router\PriorityList;
use Laminas\Router\RouteInterface;
use Laminas\Router\RouteMatch;
use Laminas\Router\RoutePluginManager;
use Laminas\Stdlib\RequestInterface;
use Laminas\Uri\Http;

use function array_diff_key;
use function array_flip;
use function assert;
use function count;
use function is_array;
use function is_bool;
use function is_object;
use function is_string;
use function method_exists;
use function strlen;

/**
 * @implements HttpNestedRoutesCapableInterface<HttpRouteInterface>
 */
final class Part implements HttpRouteInterface, HttpNestedRoutesCapableInterface
{
    /**
     * @internal
     * @deprecated Since 3.9.0 This property will be removed or made private in version 4.0
     */
    public int|null $priority = null;

    private readonly HttpRouteInterface $route;

    private readonly TreeRouteStack $childStack;

    /**
     * Create a new part route.
     *
     * @param ArrayObject<string, HttpRouteInterface> $prototypes
     * @param array<non-empty-string, array|HttpRouteInterface> $childRoutes
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(
        RoutePluginManager $routePluginManager,
        ArrayObject $prototypes,
        HttpRouteInterface|array|string $routes = [],
        array $defaultParams = [],
        /**
         * Whether the route may terminate.
         */
        private readonly bool $mayTerminate = false,
        private array $childRoutes = [],
    ) {
        $specificationFactory = new HttpRouteSpecificationFactory($routePluginManager, $prototypes);
        $this->childStack     = new TreeRouteStack($routePluginManager, $prototypes, [], []);

        if (! is_object($routes)) {
            $routes = $specificationFactory->createFromSpecification($routes);
        }

        if ($routes instanceof self) {
            throw new Exception\InvalidArgumentException('Base route may not be a part route');
        }

        assert($routes instanceof HttpRouteInterface);

        $this->route = $routes;
    }

    /**
     * @inheritDoc
     * @throws Exception\InvalidArgumentException
     */
    public static function factory(array $options = []): self
    {
        $route        = $options['route'] ?? null;
        $routePlugins = $options['route_plugins'] ?? null;
        /** @var ArrayObject<string, HttpRouteInterface> $prototypes */
        $prototypes   = $options['prototypes'] ?? new ArrayObject();
        $mayTerminate = $options['may_terminate'] ?? false;
        /** @var array<non-empty-string, array|HttpRouteInterface> $childRoutes */
        $childRoutes = $options['child_routes'] ?? [];

        if (! $routePlugins instanceof RoutePluginManager) {
            throw new Exception\InvalidArgumentException('Missing "route_plugins" in options array');
        }

        if ($route === null) {
            throw new Exception\InvalidArgumentException('Missing "route" in options array');
        }

        assert(is_bool($mayTerminate));
        assert(is_array($route) || is_string($route) || $route instanceof HttpRouteInterface);

        return new self(
            $routePlugins,
            $prototypes,
            $route,
            [],
            $mayTerminate,
            $childRoutes,
        );
    }

    /** @inheritDoc */
    public function addRoutes(array $routes): void
    {
        $this->childStack->addRoutes($routes);
    }

    /** @inheritDoc */
    public function addRoute(string|int $name, int|string|array|RouteInterface $route, ?int $priority = null): void
    {
        $this->childStack->addRoute($name, $route, $priority);
    }

    /** @inheritDoc */
    public function removeRoute(string $name): void
    {
        $this->childStack->removeRoute($name);
    }

    /** @inheritDoc */
    public function setRoutes(array $routes): void
    {
        $this->childStack->setRoutes($routes);
    }

    public function getRoutes(): PriorityList
    {
        return $this->childStack->getRoutes();
    }

    /**
     * @param non-empty-string $name
     */
    public function hasRoute(string $name): bool
    {
        return $this->childStack->hasRoute($name);
    }

    /**
     * @param non-empty-string $name
     * @return HttpRouteInterface|null the route
     */
    public function getRoute(string $name): HttpRouteInterface|null
    {
        $route = $this->childStack->getRoute($name);
        assert($route === null || $route instanceof HttpRouteInterface);

        return $route;
    }

    /**
     * @param non-empty-string $name
     * @param non-empty-string $value
     */
    public function setDefaultParam(string $name, string $value): void
    {
        $this->childStack->setDefaultParam($name, $value);
    }

    /** @inheritDoc */
    public function match(
        RequestInterface $request,
        int|null $pathOffset = null,
        array $options = []
    ): RouteMatch|null {
        $pathOffset ??= 0;
        $match        = $this->route->match($request, $pathOffset, $options);

        assert($match instanceof HttpRouteMatch || $match === null);

        if ($match !== null && method_exists($request, 'getUri')) {
            if (count($this->childRoutes) !== 0) {
                $this->childStack->addRoutes($this->childRoutes);
                $this->childRoutes = [];
            }

            $nextOffset = $pathOffset + $match->getLength();

            /** @var Http $uri */
            $uri        = $request->getUri();
            $pathLength = strlen((string) $uri->getPath());

            if ($this->mayTerminate && $nextOffset === $pathLength) {
                return $match;
            }

            if (isset($options['translator']) && ! isset($options['locale'])) {
                /** @var mixed $locale */
                $locale = $match->getParam('locale');
                if (is_string($locale)) {
                    $options['locale'] = $locale;
                }
            }

            foreach ($this->childStack->getRoutes() as $name => $route) {
                assert($name !== '');
                assert($route instanceof HttpRouteInterface);
                $subMatch = $route->match($request, $nextOffset, $options);
                if ($subMatch instanceof HttpRouteMatch) {
                    if (($match->getLength() + $subMatch->getLength() + $pathOffset) === $pathLength) {
                        assert(is_string($name));

                        return $match->merge($subMatch)->setMatchedRouteName($name);
                    }
                }
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     * @throws Exception\RuntimeException
     */
    public function assemble(array $params = [], array $options = []): string
    {
        if (count($this->childRoutes) !== 0) {
            $this->childStack->addRoutes($this->childRoutes);
            $this->childRoutes = [];
        }

        $options['has_child'] = isset($options['name']);

        if (isset($options['translator']) && ! isset($options['locale']) && isset($params['locale'])) {
            $options['locale'] = $params['locale'];
        }

        $path   = $this->route->assemble($params, $options);
        $params = array_diff_key($params, array_flip($this->route->getAssembledParams()));

        if (! isset($options['name'])) {
            if (! $this->mayTerminate) {
                throw new Exception\RuntimeException('Part route may not terminate');
            }

            return $path;
        }

        unset($options['has_child']);
        $options['only_return_path'] = true;
        return $path . $this->childStack->assemble($params, $options);
    }

    /** @inheritDoc */
    public function getAssembledParams(): array
    {
        // Part routes may not occur as base route of other part routes, so we
        // don't have to return anything here.
        return [];
    }
}
