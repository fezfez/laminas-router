<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\Exception\InvalidArgumentException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

use function is_int;
use function is_string;

final readonly class PlaceholderFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null
    ): Placeholder {
        $options ??= [];
        $name     = $options['name'] ?? null;
        $defaults = $options['defaults'] ?? [];
        $priority = $options['priority'] ?? null;

        if (! is_string($name)) {
            throw new InvalidArgumentException('Missing "name" in options array');
        }

        if ($priority !== null && ! is_int($priority)) {
            throw new InvalidArgumentException('Invalid "priority" option');
        }

        return new Placeholder($name, $defaults, $priority);
    }
}
