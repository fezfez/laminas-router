<?php

declare(strict_types=1);

namespace Laminas\Router\Http\RouteDefinition;

/**
 * @internal
 *
 * @psalm-internal \Laminas\Router
 * @psalm-internal \LaminasTest\Router
 */
final readonly class RouteDefinitionParameter implements RouteDefinitionPartInterface
{
    /**
     * @param non-empty-string $name
     */
    public function __construct(
        public string $name,
        public string|null $delimiter
    ) {
    }
}
