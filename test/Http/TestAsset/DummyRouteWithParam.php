<?php

declare(strict_types=1);

namespace LaminasTest\Router\Http\TestAsset;

use Laminas\Router\Http\HttpRouteMatch;
use Laminas\Stdlib\RequestInterface;

use function array_key_exists;

/**
 * Dummy route.
 */
final class DummyRouteWithParam extends DummyRoute
{
    /** @inheritDoc */
    public function match(
        RequestInterface $request,
        int|null $pathOffset = null,
        array $options = []
    ): HttpRouteMatch {
        return new HttpRouteMatch(['foo' => 'bar'], -4);
    }

    /** @inheritDoc */
    public function assemble(array $params = [], array $options = []): string
    {
        return array_key_exists('foo', $params) ? (string) $params['foo'] : '';
    }

    /** @inheritDoc */
    public static function factory(array $options = []): self
    {
        return new self();
    }
}
