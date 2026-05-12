<?php

declare(strict_types=1);

namespace LaminasTest\Router\TestAsset;

use Laminas\Router\RouteInterface;
use Laminas\Router\RouteMatch;
use Laminas\Stdlib\RequestInterface;

/**
 * Dummy route.
 */
class DummyRoute implements RouteInterface
{
    public ?int $priority = null;

    /** @inheritDoc */
    public function match(RequestInterface $request): RouteMatch
    {
        return new RouteMatch([]);
    }

    /** @inheritDoc */
    public function assemble(array $params = [], array $options = []): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public static function factory(array $options = []): self
    {
        return new self();
    }
}
