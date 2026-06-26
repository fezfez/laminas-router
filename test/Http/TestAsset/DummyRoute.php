<?php

declare(strict_types=1);

namespace LaminasTest\Router\Http\TestAsset;

use Laminas\Router\AssembledUrl;
use Laminas\Router\Http\HttpRouteInterface;
use Laminas\Router\Http\HttpRouteMatch;
use Psr\Http\Message\RequestInterface;

/**
 * Dummy route.
 */
final class DummyRoute implements HttpRouteInterface
{
    /** @inheritDoc */
    public function match(
        RequestInterface $request,
        int|null $pathOffset = null,
        array $options = []
    ): HttpRouteMatch {
        return new HttpRouteMatch(['offset' => $pathOffset], -4);
    }

    /** @inheritDoc */
    public function assemble(array $params = [], array $options = []): AssembledUrl
    {
        return new AssembledUrl();
    }

    /** @inheritDoc */
    public static function factory(array $options = []): self
    {
        return new self();
    }

    public function getPriority(): ?int
    {
        return null;
    }
}
