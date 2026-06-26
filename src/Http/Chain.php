<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\AssembledUrl;
use Laminas\Router\Exception;
use Laminas\Router\Http\HttpRouteMatch;
use Laminas\Router\RoutePluginManager;
use Override;
use Psr\Http\Message\RequestInterface;

use function array_diff_key;
use function array_flip;
use function array_key_last;
use function array_reverse;
use function assert;
use function is_array;
use function is_bool;
use function strlen;

/**
 * @template TRoute of HttpRouteInterface
 * @template-extends TreeRouteStack<TRoute>
 */
final readonly class Chain extends TreeRouteStack implements HttpRouteInterface
{
    /**
     * Create a new part route.
     *
     * @param array<array-key, array|TRoute> $routes
     * @param array<non-empty-string, non-empty-string> $defaultParams
     */
    public function __construct(
        RoutePluginManager $routePluginManager,
        array $routes = [],
        array $defaultParams = [],
        ?int $priority = null,
    ) {
        parent::__construct($routePluginManager, array_reverse($routes), $defaultParams, $priority);
    }

    /**
     * @inheritDoc
     * @throws Exception\InvalidArgumentException
     */
    #[Override]
    public static function factory(array $options = []): self
    {
        $routePlugins = $options['route_plugins'] ?? null;

        if (! isset($options['routes']) || ! is_array($options['routes'])) {
            throw new Exception\InvalidArgumentException('Missing "routes" in options array');
        }

        if (! $routePlugins instanceof RoutePluginManager) {
            throw new Exception\InvalidArgumentException('Missing "route_plugins" in options array');
        }

        /** @psalm-var array<non-empty-string, array|TRoute> $routes */
        $routes = $options['routes'];
        /** @psalm-var RoutePluginManager $routePlugins */

        /** @psalm-var int|null $priority */
        $priority = $options['priority'] ?? null;

        return new self(
            $routePlugins,
            $routes,
            [],
            $priority,
        );
    }

    /** @inheritDoc */
    #[Override]
    public function match(RequestInterface $request, int|null $pathOffset = null, array $options = []): ?HttpRouteMatch
    {
        $mustTerminate = $pathOffset === null;
        $pathOffset  ??= 0;
        $match         = new HttpRouteMatch([]);
        $pathLength    = strlen($request->getUri()->getPath());

        foreach ($this->routes->getAsArray() as $route) {
            $subMatch = $route->match($request, $pathOffset, $options);

            if ($subMatch === null) {
                return null;
            }

            assert($subMatch instanceof HttpRouteMatch);

            $match       = $match->merge($subMatch);
            $pathOffset += $subMatch->getLength();
        }

        if ($mustTerminate && $pathOffset !== $pathLength) {
            return null;
        }

        return $match;
    }

    /** @inheritDoc */
    #[Override]
    public function assemble(array $params = [], array $options = []): AssembledUrl
    {
        $finalResult  = new AssembledUrl();
        $routes       = [...$this->routes->getAsArray()];
        $lastRouteKey = array_key_last($routes);

        foreach ($routes as $key => $route) {
            $chainOptions = $options;
            $hasChild     = isset($options['has_child']) && is_bool($options['has_child']) && $options['has_child'];

            $chainOptions['has_child'] = $hasChild || $key !== $lastRouteKey;

            $assembledUrl = $route->assemble($params, $chainOptions);
            $finalResult  = $finalResult->merge($assembledUrl);

            $params = array_diff_key($params, array_flip($assembledUrl->assembledParams));
        }

        return $finalResult;
    }
}
