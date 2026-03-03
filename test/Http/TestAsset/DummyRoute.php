<?php

declare(strict_types=1);

namespace LaminasTest\Router\Http\TestAsset;

use Laminas\Router\Http\HttpRouteInterface;
use Laminas\Router\Http\HttpRouteMatch;
use Laminas\Router\RouteMatch;
use Laminas\Stdlib\RequestInterface;

/**
 * Dummy route.
 */
class DummyRoute implements HttpRouteInterface
{
    /**
     * @inheritDoc
     */
    public function match(
        RequestInterface $request,
        int|null $pathOffset = null,
        array $options = []
    ): RouteMatch {
        return new HttpRouteMatch(['offset' => $pathOffset], -4);
    }

    /**
     * @inheritDoc
     */
    public function assemble(array $params = [], array $options = []): mixed
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public static function factory(iterable $options = []): static
    {
        return new static();
    }

    /**
     * @inheritDoc
     */
    public function getAssembledParams(): array
    {
        return [];
    }
}
