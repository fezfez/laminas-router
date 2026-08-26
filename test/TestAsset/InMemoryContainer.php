<?php

declare(strict_types=1);

namespace LaminasTest\Router\TestAsset;

use Laminas\Router\Exception\RuntimeException;
use Psr\Container\ContainerInterface;

use function array_key_exists;
use function sprintf;

/**
 * Minimal PSR-11 container for test assembly of route builders.
 */
final class InMemoryContainer implements ContainerInterface
{
    /** @var array<string, mixed> */
    private array $services = [];

    public function set(string $id, mixed $service): void
    {
        $this->services[$id] = $service;
    }

    public function get(string $id): mixed
    {
        if (! array_key_exists($id, $this->services)) {
            throw new RuntimeException(sprintf('Service "%s" was not found in the in-memory container', $id));
        }

        return $this->services[$id];
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->services);
    }
}
