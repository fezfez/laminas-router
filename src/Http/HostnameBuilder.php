<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\Exception;
use Laminas\Router\RouteBuilderInterface;
use Laminas\Router\RouteInterface;

use function is_string;

/**
 * @implements RouteBuilderInterface<Hostname>
 */
final readonly class HostnameBuilder implements RouteBuilderInterface
{
    public function build(array $options = []): RouteInterface
    {
        $name  = $options['name'] ?? null;
        $route = $options['route'] ?? null;
        /** @psalm-var array<non-empty-string, string> $constraints */
        $constraints = $options['constraints'] ?? [];
        /** @psalm-var array<string, string|int|float|null> $defaults */
        $defaults = $options['defaults'] ?? [];
        /** @psalm-var int|null $priority */
        $priority = $options['priority'] ?? null;

        if (! is_string($route)) {
            throw new Exception\InvalidArgumentException('Missing "route" in options array');
        }

        if (! is_string($name)) {
            throw new Exception\InvalidArgumentException('Missing "name" in options array');
        }

        return new Hostname($name, $route, $constraints, $defaults, $priority);
    }
}
