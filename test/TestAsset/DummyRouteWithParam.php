<?php

declare(strict_types=1);

namespace LaminasTest\Router\TestAsset;

use Laminas\Router\AssembledUrl;
use Laminas\Router\RouteInterface;
use Laminas\Router\RouteMatch;
use Psr\Http\Message\RequestInterface;

use function array_key_exists;

/**
 * Dummy route.
 */
final readonly class DummyRouteWithParam implements RouteInterface
{
    public function __construct(private int|null $priority = null)
    {
    }

    /** @inheritDoc */
    public function match(RequestInterface $request): RouteMatch
    {
        return new RouteMatch(['foo' => 'bar']);
    }

    /** @inheritDoc */
    public function assemble(array $params = [], array $options = []): AssembledUrl
    {
        return new AssembledUrl(array_key_exists('foo', $params) ? (string) $params['foo'] : '');
    }

    /** @inheritDoc */
    public static function factory(array $options = []): static
    {
        /** @psalm-var int|null $priority */
        $priority = $options['priority'] ?? null;

        return new self($priority);
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }
}
