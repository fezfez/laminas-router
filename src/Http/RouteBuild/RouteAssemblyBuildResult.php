<?php

declare(strict_types=1);

namespace Laminas\Router\Http\RouteBuild;

/**
 * @internal
 *
 * @psalm-internal \Laminas\Router
 * @psalm-internal \LaminasTest\Router
 */
final readonly class RouteAssemblyBuildResult
{
    /**
     * @param non-empty-string|null $segment
     * @param list<non-empty-string> $assembledParams
     */
    public function __construct(
        public string|null $segment,
        public array $assembledParams = []
    ) {
    }
}
