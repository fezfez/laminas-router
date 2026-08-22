<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\Exception\InvalidArgumentException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

use function is_int;
use function is_string;

final readonly class HostnameFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, string $requestedName, ?array $options = null): Hostname
    {
        $options ??= [];
        $name = $options['name'] ?? null;
        $route = $options['route'] ?? null;
        $constraints = $options['constraints'] ?? [];
        $defaults = $options['defaults'] ?? [];
        $priority = $options['priority'] ?? null;
        if (! is_string($name)) {
            throw new InvalidArgumentException('Missing "name" in options array');
        }
        if (! is_string($route)) {
            throw new InvalidArgumentException('Missing "route" in options array');
        }
        if ($priority !== null && ! is_int($priority)) {
            throw new InvalidArgumentException('Invalid "priority" option');
        }
        return new Hostname($name, $route, $constraints, $defaults, $priority);
    }
}
