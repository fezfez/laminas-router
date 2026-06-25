<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\AssembledUrl;
use Laminas\Router\Exception;
use Laminas\Router\RouteMatch;
use Laminas\Router\RoutePluginManager;
use Override;
use Psr\Http\Message\RequestInterface;

use function array_diff_key;
use function array_flip;
use function assert;
use function is_array;
use function is_bool;
use function is_int;
use function is_string;
use function strlen;

/**
 * @template TRoute of HttpRouteInterface
 * @template-extends TreeRouteStack<TRoute>
 */
final readonly class Part extends TreeRouteStack implements HttpRouteInterface
{
    /**
     * RouteInterface to match.
     *
     * @var TRoute
     */
    private HttpRouteInterface $route;

    /**
     * Create a new part route.
     *
     * @param TRoute|array           $routes
     * @param array<non-empty-string, array|TRoute> $childRoutes
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(
        RoutePluginManager $routePluginManager,
        HttpRouteInterface|array $routes = [],
        array $defaultParams = [],
        ?int $priority = null,
        /**
         * Whether the route may terminate.
         */
        private bool $mayTerminate = false,
        array $childRoutes = [],
    ) {
        parent::__construct($routePluginManager, priority: $priority);

        if (is_array($routes)) {
            $routes = $this->routeFromArray($routes);
        }

        if ($routes instanceof self) {
            throw new Exception\InvalidArgumentException('Base route may not be a part route');
        }

        $this->route = $routes;

        if ($childRoutes !== []) {
            $this->addRoutes($childRoutes);
        }
    }

    /**
     * @inheritDoc
     * @throws Exception\InvalidArgumentException
     */
    #[Override]
    public static function factory(array $options = []): self
    {
        $routes       = $options['route'] ?? null;
        $routePlugins = $options['route_plugins'] ?? null;
        $mayTerminate = $options['may_terminate'] ?? false;
        /** @var array<non-empty-string, TRoute> $childRoutes */
        $childRoutes = $options['child_routes'] ?? [];

        if (! $routePlugins instanceof RoutePluginManager) {
            throw new Exception\InvalidArgumentException('Missing "route_plugins" in options array');
        }

        if ($routes === null) {
            throw new Exception\InvalidArgumentException('Missing "route" in options array');
        }

        assert(is_bool($mayTerminate));
        assert(is_array($routes) || $routes instanceof HttpRouteInterface);

        /** @psalm-var int|null $priority */
        $priority = $options['priority'] ?? null;

        /** @psalm-var TRoute|array $routes */

        return new self(
            $routePlugins,
            $routes,
            [],
            is_int($priority) ? $priority : null,
            $mayTerminate,
            $childRoutes,
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

        if ($match !== null) {
            $nextOffset = $pathOffset + $match->getLength();

            $pathLength = strlen($request->getUri()->getPath());

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
                assert(is_string($name));
                assert($route instanceof HttpRouteInterface);
                $subMatch = $route->match($request, $nextOffset, $options);
                if ($subMatch instanceof HttpRouteMatch) {
                    if (($match->getLength() + $subMatch->getLength() + $pathOffset) === $pathLength) {
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
    #[Override]
    public function assemble(array $params = [], array $options = []): AssembledUrl
    {
        $options['has_child'] = isset($options['name']);

        if (isset($options['translator']) && ! isset($options['locale']) && isset($params['locale'])) {
            $options['locale'] = $params['locale'];
        }

        $uri    = $this->route->assemble($params, $options);
        $params = array_diff_key($params, array_flip($uri->assembledParams));

        if (! isset($options['name'])) {
            if (! $this->mayTerminate) {
                throw new Exception\RuntimeException('Part route may not terminate');
            }

            return $uri;
        }

        unset($options['has_child']);
        $options['only_return_path'] = true;

        return $uri->merge(parent::assemble($params, $options));
    }
}
