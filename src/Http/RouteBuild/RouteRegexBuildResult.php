<?php

declare(strict_types=1);

namespace Laminas\Router\Http\RouteBuild;

/**
 * @internal
 *
 * @psalm-internal \Laminas\Router
 * @psalm-internal \LaminasTest\Router
 */
final readonly class RouteRegexBuildResult
{
    /**
     * @param array<string, string> $paramMap
     * @param list<string> $translationKeys
     */
    public function __construct(
        public string $regex,
        public array $paramMap,
        public array $translationKeys = [],
        public int $nextGroupIndex = 1,
    ) {
    }
}
