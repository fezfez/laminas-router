<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use ArrayObject;
use Laminas\Router\Exception;
use Laminas\Router\Exception\RuntimeException;
use Laminas\Router\RouteInterface;
use Laminas\Router\RoutePluginManager;

use function array_merge;
use function assert;
use function is_array;
use function is_string;
use function property_exists;
use function sprintf;

/**
 * Turns HTTP route configuration arrays (and prototype names) into route objects.
 */
final class HttpRouteSpecificationFactory
{
    /**
     * @param ArrayObject<string, HttpRouteInterface> $prototypes
     */
    public function __construct(
        private readonly RoutePluginManager $routePluginManager,
        private readonly ArrayObject $prototypes,
    ) {
    }

    /**
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     */
    public function createFromSpecification(string|array $specs): HttpRouteInterface
    {
        if (is_string($specs)) {
            return $this->getPrototype($specs);
        }

        /** @var array<string, mixed> $specsArray */
        $specsArray = $specs;

        if (isset($specsArray['chain_routes'])) {
            if (! is_array($specsArray['chain_routes'])) {
                throw new Exception\InvalidArgumentException('Chain routes must be an array');
            }

            $chainRoutes = array_merge([$specsArray], $specsArray['chain_routes']);
            if (isset($chainRoutes[0]['chain_routes'])) {
                unset($chainRoutes[0]['chain_routes']);
            }

            if (isset($specsArray['child_routes']) && isset($chainRoutes[0]['child_routes'])) {
                unset($chainRoutes[0]['child_routes']);
            }

            $options = [
                'routes'        => $chainRoutes,
                'route_plugins' => $this->routePluginManager,
                'prototypes'    => $this->prototypes,
            ];

            $route = $this->routePluginManager->build(Chain::class, $options);
        } else {
            $route = $this->createFromTypedArray($specsArray);
        }

        assert($route instanceof RouteInterface);

        if (! $route instanceof HttpRouteInterface) {
            throw new Exception\RuntimeException('Given route does not implement HTTP route interface');
        }

        if (isset($specsArray['child_routes'])) {
            $options = [
                'route'         => $route,
                'may_terminate' => isset($specsArray['may_terminate']) && $specsArray['may_terminate'] === true,
                'child_routes'  => $specsArray['child_routes'],
                'route_plugins' => $this->routePluginManager,
                'prototypes'    => $this->prototypes,
            ];

            $priority = $route->priority ?? null;

            /** @var Part $builtPart */
            $builtPart           = $this->routePluginManager->build(Part::class, $options);
            $builtPart->priority = $priority;

            $route = $builtPart;
        }

        return $route;
    }

    private function getPrototype(string $name): HttpRouteInterface
    {
        if (! property_exists($this->prototypes, $name)) {
            throw new RuntimeException(sprintf('Could not find prototype with name %s', $name));
        }

        $route = $this->prototypes[$name];
        assert($route instanceof HttpRouteInterface);

        return $route;
    }

    /**
     * @param array<string, mixed> $specs
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
        assert($route instanceof RouteInterface);

        if (isset($specs['priority'])) {
            $route->priority = $specs['priority'];
        }

        return $route;
    }
}
