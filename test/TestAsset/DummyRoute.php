<?php

declare(strict_types=1);

namespace LaminasTest\Router\TestAsset;

use Laminas\Router\AssembledUrl;
use Laminas\Router\RouteInterface;
use Laminas\Router\RouteMatch;
use Psr\Http\Message\RequestInterface;

/**
 * Dummy route.
 */
final readonly class DummyRoute implements RouteInterface
{
    public function __construct(private int|null $priority = null)
    {
    }

    /** @inheritDoc */
    public function match(RequestInterface $request): RouteMatch
    {
        return new RouteMatch([]);
    }

    /** @inheritDoc */
    public function assemble(array $params = [], array $options = []): AssembledUrl
    {
        return new AssembledUrl();
    }

    /** @inheritDoc */
    public static function factory(array $options = []): static
    {
        /** @psalm-var int|null $priority */
        $priority = $options['priority'] ?? null;

        return new static($priority);
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }
}
