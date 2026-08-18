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
     * Create a new route with given options.
     */
    public static function factory(array $options = []): self;

    /**
     * Match a given request.
     */
    public function match(RequestInterface $request): RouteMatchInterface|null;

    /**
     * Assemble the route.
     *
     * @param array<string, string|int|float|null> $params
     */
    public function assemble(array $params = [], array $options = []): AssembledUrl;

    public function getPriority(): int|null;
}
