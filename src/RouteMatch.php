<?php

declare(strict_types=1);

namespace Laminas\Router;

use function array_key_exists;

/**
 * RouteInterface match.
 */
final readonly class RouteMatch implements RouteMatchInterface
{
    /**
     * Create a RouteMatch with given parameters.
     *
     * @param array<string, string|int|null> $params
     */
    public function __construct(
        /**
         * Match parameters.
         */
        private array $params,
        private string $matchedRouteName
    ) {
    }

    /**
     * Get name of matched route.
     */
    public function getMatchedRouteName(): string
    {
        return $this->matchedRouteName;
    }

    /**
     * Set a parameter.
     */
    public function setParam(string $name, string $value): self
    {
        $params        = $this->params;
        $params[$name] = $value;

        return new self($params, $this->matchedRouteName);
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
    public function setDefaults(array $defaults): self
    {
        $params = $this->params;

        foreach ($defaults as $paramName => $value) {
            if ($this->getParam($paramName) === null) {
                $params[$paramName] = $value;
            }
        }

        return new self($params, $this->matchedRouteName);
    }
}
