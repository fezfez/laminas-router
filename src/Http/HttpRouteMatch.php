<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\RouteMatchInterface;

use function array_key_exists;
use function array_merge;
use function implode;

/**
 * Part route match.
 */
final readonly class HttpRouteMatch implements RouteMatchInterface
{
    /**
     * Create a part RouteMatch with given parameters and length.
     *
     * @param array<string, string|int|float|null> $params
     */
    public function __construct(
        /**
         * Match parameters.
         */
        private array $params,
        private string $matchedRouteName,
        /**
         * Length of the matched path.
         */
        private int $length = 0,
    ) {
    }

    /**
     * Merge parameters from another match.
     */
    public function merge(RouteMatchInterface $match): self
    {
        $params = array_merge($this->params, $match->getParams());
        $length = $this->length + $match->getLength();

        $routeName = [];

        if ($this->matchedRouteName !== '') {
            $routeName[] = $this->matchedRouteName;
        }

        $toMerge = $match->getMatchedRouteName();

        if ($toMerge !== '') {
            $routeName[] = $toMerge;
        }

        $matchedRouteName = implode('/', $routeName);

        return new self($params, $matchedRouteName, $length);
    }

    /**
     * Get the matched path length.
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * Get name of matched route.
     */
    public function getMatchedRouteName(): string
    {
        return $this->matchedRouteName;
    }

    /**
     * Get all parameters.
     *
     * @return array<string, string|int|float|null>
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Get a specific parameter.
     */
    public function getParam(string $name, ?string $default = null): int|float|string|null
    {
        if (array_key_exists($name, $this->params)) {
            return $this->params[$name];
        }

        return $default;
    }
}
