<?php

declare(strict_types=1);

namespace Laminas\Router;

interface RouteMatchInterface
{
    /**
     * Get the matched path length.
     */
    public function getLength(): int;

    /**
     * Get name of matched route.
     */
    public function getMatchedRouteName(): string;

    /**
     * Get all parameters.
     *
     * @return array<string, string|int|float|null>
     */
    public function getParams(): array;

    /**
     * Get a specific parameter.
     */
    public function getParam(string $name, ?string $default = null): int|float|string|null;

    /**
     * Merge parameters from another match.
     */
    public function merge(RouteMatchInterface $match): self;
}
