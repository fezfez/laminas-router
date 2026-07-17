<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\AssembledUrl;
use Laminas\Router\Exception;
use Laminas\Router\Http\HttpRouteMatch;
use Laminas\Router\RouteMatchInterface;
use Override;
use Psr\Http\Message\RequestInterface;

use function is_string;

/**
 * Scheme route.
 */
final readonly class Scheme implements HttpRouteInterface
{
    /**
     * Create a new scheme route.
     *
     * @param array<string, string|int|float|null> $defaults
     */
    public function __construct(
        private string $name,
        /**
         * Scheme to match.
         *
         * @var non-empty-string
         */
        private string $scheme,
        /**
         * Default values.
         */
        private array $defaults = [],
        private int|null $priority = null
    ) {
    }

    #[Override]
    public static function factory(array $options = []): self
    {
        $name = $options['name'] ?? null;
        /** @psalm-var string|null $scheme */
        $scheme = $options['scheme'] ?? null;
        /** @psalm-var array<string, string|int|float|null>  $defaults */
        $defaults = $options['defaults'] ?? [];
        /** @psalm-var int|null $priority */
        $priority = $options['priority'] ?? null;

        if (! is_string($scheme) || $scheme === '') {
            throw new Exception\InvalidArgumentException('Missing "scheme" in options array');
        }

        if (! is_string($name)) {
            throw new Exception\InvalidArgumentException('Missing "name" in options array');
        }

        return new self($name, $scheme, $defaults, $priority);
    }

    /** @inheritDoc */
    #[Override]
    public function match(
        RequestInterface $request,
        int|null $pathOffset = null,
        array $options = []
    ): ?RouteMatchInterface {
        if ($request->getUri()->getScheme() !== $this->scheme) {
            return null;
        }

        return new HttpRouteMatch($this->defaults, $this->name, 0);
    }

    /** @inheritDoc */
    #[Override]
    public function assemble(array $params = [], array $options = []): AssembledUrl
    {
        return new AssembledUrl(scheme:$this->scheme);
    }

    #[Override]
    public function getPriority(): ?int
    {
        return $this->priority;
    }
}
