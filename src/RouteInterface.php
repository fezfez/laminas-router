<?php

declare(strict_types=1);

namespace Laminas\Router;

use Laminas\Router\AssembledUrl;
use Psr\Http\Message\RequestInterface;

/**
 * RouteInterface interface.
 */
interface RouteInterface
{
    /**
     * Match a given request.
     */
    public function match(RequestInterface $request): RouteMatchInterface|null;

    /**
     * Assemble the route.
     *
     * @param array<string, string|int|float|null> $params
     * @param array<string, mixed> $options
     */
    public function assemble(array $params = [], array $options = []): AssembledUrl;

    public function getPriority(): int|null;
}
