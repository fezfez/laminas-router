<?php

declare(strict_types=1);

namespace LaminasTest\Router\TestAsset;

use InvalidArgumentException;
use Laminas\Router\AssembledUrl;
use Laminas\Router\RouteInterface;
use Laminas\Router\RouteMatch;
use Laminas\Router\RouteMatchInterface;
use Psr\Http\Message\RequestInterface;

use function is_string;

/**
 * Dummy route.
 */
final readonly class DummyRoute implements RouteInterface
{
    public function __construct(
        private string $name,
        private int|null $priority = null
    ) {
    }

    /** @inheritDoc */
    public function match(RequestInterface $request): RouteMatchInterface
    {
        return new RouteMatch([], $this->name);
    }

    /** @inheritDoc */
    public function assemble(array $params = [], array $options = []): AssembledUrl
    {
        return new AssembledUrl();
    }

    /** @inheritDoc */
    public static function factory(array $options = []): self
    {
        /** @psalm-var int|null $priority */
        $priority = $options['priority'] ?? null;
        $name     = $options['name'] ?? null;

        if (! is_string($name)) {
            throw new InvalidArgumentException('Missing "name" in options array');
        }

        return new self($name, $priority);
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }
}
