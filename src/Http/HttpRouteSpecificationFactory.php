<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use ArrayObject;
use Laminas\Router\Exception;
use Laminas\Router\Exception\RuntimeException;
use Laminas\Router\RouteInterface;
use Laminas\Router\RoutePluginManager;

use function array_merge;
use function is_array;
use function is_string;
use function property_exists;
use function sprintf;

/**
 * Turns HTTP route configuration arrays (and prototype names) into route objects.
 *
 * @template TRoute of HttpRouteInterface
 */
final class HttpRouteSpecificationFactory
{
    /**
     * @param ArrayObject<string, TRoute> $prototypes
     */
    public function __construct(
        private readonly RoutePluginManager $routePluginManager,
        private readonly ArrayObject $prototypes,
    ) {
    }

    /**
     * @return TRoute
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     */
    public function createFromSpecification(string|array $specs): RouteInterface
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
            $route = $this->createFromTypedArray($specs);
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
     * @return TRoute
     * @throws Exception\InvalidArgumentException
     */
    private function getPrototype(string $name): RouteInterface
    {
        if (! property_exists($this->prototypes, $name)) {
            throw new RuntimeException(sprintf('Could not find prototype with name %s', $name));
        }

        return $this->prototypes[$name];
    }

    /**
     * @param array<string, mixed> $specs
     * @return TRoute
     * @throws Exception\InvalidArgumentException
     */
    private function createFromTypedArray(array $specs): RouteInterface
    {
        $type = $specs['type'] ?? null;
        /** @var array<string, string> $option */
        $option = $specs['options'] ?? [];

        if (! is_string($type) || $type === '') {
            throw new Exception\InvalidArgumentException('Missing "type" option');
        }

        $route = $this->routePluginManager->build($type, $option);

        if (isset($specs['priority'])) {
            $route->priority = $specs['priority'];
        }

        return $route;
    }
}
