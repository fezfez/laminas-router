<?php

declare(strict_types=1);

namespace Laminas\Router\Http\RouteDefinition;

/**
 * @internal
 */
final readonly class RouteDefinitionLiteral implements RouteDefinitionPartInterface
{
    public function __construct(
        public string $literal
    ) {
    }
}
