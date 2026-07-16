<?php

declare(strict_types=1);

namespace Laminas\Router;

interface RouteMatchInterface
{
    /**
     * Get name of matched route.
     */
    public function getMatchedRouteName(): string;

    /**
     * Set a parameter.
     */
    public function setParam(string $name, string $value): self;

    /**
     * Get all parameters.
     *
     * @return array<string, string|int|null>
     */
    public function getParams(): array;

    /**
     * Get a specific parameter.
     */
    public function getParam(string $name, ?string $default = null): int|string|null;

    /**
     * @param array<non-empty-string, non-empty-string> $defaults
     */
    public function setDefaults(array $defaults): self;
}
