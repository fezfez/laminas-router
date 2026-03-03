<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\RouteMatch;
use Override;

use function array_merge;

/**
 * Part route match.
 *
 * @final
 */
class HttpRouteMatch extends RouteMatch
{
    /**
     * Create a part RouteMatch with given parameters and length.
     */
    public function __construct(
        array $params,
        /**
         * Length of the matched path.
         */
        protected int $length = 0
    ) {
        parent::__construct($params);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function setMatchedRouteName(string $name): static
    {
        if ($this->matchedRouteName === null) {
            $this->matchedRouteName = $name;
        } else {
            $this->matchedRouteName = $name . '/' . $this->matchedRouteName;
        }

        return $this;
    }

    /**
     * Merge parameters from another match.
     */
    public function merge(HttpRouteMatch $match): static
    {
        $this->params  = array_merge($this->params, $match->getParams());
        $this->length += $match->getLength();

        $this->matchedRouteName = $match->getMatchedRouteName();

        return $this;
    }

    /**
     * Get the matched path length.
     */
    public function getLength(): int
    {
        return $this->length;
    }
}
