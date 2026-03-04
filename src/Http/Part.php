<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use ArrayObject;
use Laminas\Router\Exception;
use Laminas\Router\RouteMatch;
use Laminas\Router\RoutePluginManager;
use Laminas\Stdlib\RequestInterface;
use Laminas\Uri\Http;
use Override;

use function array_diff_key;
use function array_flip;
use function assert;
use function count;
use function is_array;
use function is_bool;
use function is_string;
use function method_exists;
use function strlen;

/**
 * @template TRoute of HttpRouteInterface
 * @template-extends TreeRouteStack<TRoute>
 */
final class Part extends TreeRouteStack implements HttpRouteInterface
{
    /**
     * RouteInterface to match.
     *
     * @var TRoute
     */
    private readonly HttpRouteInterface $route;

    /**
     * Create a new part route.
     *
     * @param TRoute|array|string           $route
     * @param ArrayObject<string, TRoute> $prototypes
     * @param array<non-empty-string, array|TRoute> $childRoutes
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(
        HttpRouteInterface|array|string $route,
        /**
         * Whether the route may terminate.
         */
        private readonly bool $mayTerminate,
        RoutePluginManager $routePlugins,
        private array $childRoutes,
        ArrayObject $prototypes
    ) {
        parent::__construct($routePlugins, $prototypes);

        if (! $route instanceof HttpRouteInterface) {
            $route = $this->routeFromArray($route);
        }

        if ($route instanceof self) {
            throw new Exception\InvalidArgumentException('Base route may not be a part route');
        }

        $this->route = $route;
    }

    /**
     * @inheritDoc
     * @throws Exception\InvalidArgumentException
     */
    #[Override]
    public static function factory(array $options = []): static
    {
        $route        = $options['route'] ?? null;
        $routePlugins = $options['route_plugins'] ?? null;
        /** @var ArrayObject<string, TRoute> $prototypes */
        $prototypes   = $options['prototypes'] ?? new ArrayObject();
        $mayTerminate = $options['may_terminate'] ?? false;
        /** @var array<non-empty-string, TRoute> $childRoutes */
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
            $route,
            $mayTerminate,
            $routePlugins,
            $childRoutes,
            $prototypes
        );
    }

    /** @inheritDoc */
    #[Override]
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
                $this->addRoutes($this->childRoutes);
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

            foreach ($this->routes as $name => $route) {
                assert($route instanceof HttpRouteInterface);
                $subMatch = $route->match($request, $nextOffset, $options);
                if ($subMatch instanceof HttpRouteMatch) {
                    if (($match->getLength() + $subMatch->getLength() + $pathOffset) === $pathLength) {
                        return $match->merge($subMatch)->setMatchedRouteName((string) $name);
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
    #[Override]
    public function assemble(array $params = [], array $options = []): string
    {
        if (count($this->childRoutes) !== 0) {
            $this->addRoutes($this->childRoutes);
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
        return $path . parent::assemble($params, $options);
    }

    /** @inheritDoc */
    #[Override]
    public function getAssembledParams(): array
    {
        // Part routes may not occur as base route of other part routes, so we
        // don't have to return anything here.
        return [];
    }
}
