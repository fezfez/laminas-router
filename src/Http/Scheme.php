<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\AssembledUrl;
use Laminas\Router\Exception;
use Laminas\Router\Http\HttpRouteMatch;
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
     * @param array<string, string> $defaults
     */
    public function __construct(
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
    public static function factory(array $options = []): static
    {
        /** @psalm-var string|null $scheme */
        $scheme = $options['scheme'] ?? null;
        /** @psalm-var array<string, string> $defaults */
        $defaults = $options['defaults'] ?? [];
        /** @psalm-var int|null $priority */
        $priority = $options['priority'] ?? null;

        if (! is_string($scheme) || $scheme === '') {
            throw new Exception\InvalidArgumentException('Missing "scheme" in options array');
        }

        return new self($scheme, $defaults, $priority);
    }

    /** @inheritDoc */
    #[Override]
    public function match(RequestInterface $request, int|null $pathOffset = null, array $options = []): ?HttpRouteMatch
    {
        if ($request->getUri()->getScheme() !== $this->scheme) {
            return null;
        }

        return new HttpRouteMatch($this->defaults);
    }

    /** @inheritDoc */
    #[Override]
    public function assemble(array $params = [], array $options = []): AssembledUrl
    {
        return new AssembledUrl(scheme:$this->scheme);
    }

    /** @inheritDoc */
    #[Override]
    public function getAssembledParams(): array
    {
        return [];
    }

    #[Override]
    public function getPriority(): ?int
    {
        return $this->priority;
    }
}
