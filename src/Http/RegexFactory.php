<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\Exception\InvalidArgumentException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

use function is_int;
use function is_string;

final readonly class RegexFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, string $requestedName, ?array $options = null): Regex
    {
        $options ??= [];
        $name = $options['name'] ?? null;
        $regex = $options['regex'] ?? null;
        $spec = $options['spec'] ?? null;
        $defaults = $options['defaults'] ?? [];
        $priority = $options['priority'] ?? null;
        if (! is_string($name)) {
            throw new InvalidArgumentException('Missing "name" in options array');
        }
        if (! is_string($regex) || $regex === '') {
            throw new InvalidArgumentException('Missing "regex" in options array');
        }
        if (! is_string($spec) || $spec === '') {
            throw new InvalidArgumentException('Missing "spec" in options array');
        }
        if ($priority !== null && ! is_int($priority)) {
            throw new InvalidArgumentException('Invalid "priority" option');
        }
        return new Regex($name, $regex, $spec, $defaults, $priority);
    }
}
