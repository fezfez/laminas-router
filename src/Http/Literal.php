<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\AssembledUrl;
use Laminas\Router\Exception;
use Laminas\Router\Http\HttpRouteMatch;
use Override;
use Psr\Http\Message\RequestInterface;

use function assert;
use function is_array;
use function is_string;
use function strlen;
use function strpos;

/**
 * Literal route.
 */
final readonly class Literal implements HttpRouteInterface
{
    /**
     * Create a new literal route.
     *
     * @param  array<string, string> $defaults
     */
    public function __construct(
        /**
         * RouteInterface to match.
         */
        private string $route,
        /**
         * Default values.
         */
        private array $defaults = [],
        private int|null $priority = null
    ) {
    }

    /**
     * @inheritDoc
     * @throws Exception\InvalidArgumentException
     */
    #[Override]
    public static function factory(array $options = []): self
    {
        $route    = $options['route'] ?? null;
        $defaults = $options['defaults'] ?? [];
        /** @psalm-var int|null $priority */
        $priority = $options['priority'] ?? null;

        if (! is_string($route)) {
            throw new Exception\InvalidArgumentException('Missing "route" in options array');
        }

        assert(is_array($defaults));

        /** @psalm-var array<string, string> $defaults */

        return new self($route, $defaults, $priority);
    }

    /** @inheritDoc */
    #[Override]
    public function match(RequestInterface $request, int|null $pathOffset = null, array $options = []): ?HttpRouteMatch
    {
        $path = $request->getUri()->getPath();

        if ($pathOffset !== null) {
            if ($pathOffset >= 0 && strlen($path) >= $pathOffset && ! empty($this->route)) {
                if (strpos($path, $this->route, $pathOffset) === $pathOffset) {
                    return new HttpRouteMatch($this->defaults, strlen($this->route));
                }
            }

            return null;
        }

        if ($path === $this->route) {
            return new HttpRouteMatch($this->defaults, strlen($this->route));
        }

        if ($this->route === '/' && ($path === '' || $path === '/')) {
            return new HttpRouteMatch($this->defaults, strlen($this->route));
        }

        return null;
    }

    /** @inheritDoc */
    #[Override]
    public function assemble(array $params = [], array $options = []): AssembledUrl
    {
        return new AssembledUrl(path:$this->route);
    }

    #[Override]
    public function getPriority(): ?int
    {
        return $this->priority;
    }
}
