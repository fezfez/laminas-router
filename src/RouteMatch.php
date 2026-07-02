<?php

declare(strict_types=1);

namespace Laminas\Router;

use function array_key_exists;

/**
 * RouteInterface match.
 */
class RouteMatch
{
    /**
     * Matched route name.
     *
     * @var non-empty-string|null
     */
    protected string|null $matchedRouteName = null;

    /**
     * Create a RouteMatch with given parameters.
     *
     * @param  array<string, string|int|null> $params
     */
    public function __construct(
        /**
         * Match parameters.
         */
        protected array $params
    ) {
    }

    /**
     * Set name of matched route.
     *
     * @param non-empty-string $name
     */
    public function setMatchedRouteName(string $name): static
    {
        $this->matchedRouteName = $name;
        return $this;
    }

    /**
     * Get name of matched route.
     *
     * @return non-empty-string|null
     */
    public function getMatchedRouteName(): string|null
    {
        return $this->matchedRouteName;
    }

    /**
     * Set a parameter.
     */
    public function setParam(string $name, string $value): static
    {
        $this->params[$name] = $value;
        return $this;
    }

    /**
     * Get all parameters.
     *
     * @return array<string, string|int|null>
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Get a specific parameter.
     */
    public function getParam(string $name, ?string $default = null): int|string|null
    {
        if (array_key_exists($name, $this->params)) {
            return $this->params[$name];
        }

        return $default;
    }

    /**
     * @param array<non-empty-string, non-empty-string> $defaults
     */
    public function setDefaults(array $defaults): static
    {
        foreach ($defaults as $paramName => $value) {
            if ($this->getParam($paramName) === null) {
                $this->params[$paramName] = $value;
            }
        }

        return $this;
    }
}
