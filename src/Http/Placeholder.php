<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\AssembledUrl;
use Laminas\Router\Exception;
use Laminas\Router\Http\HttpRouteMatch;
use Override;
use Psr\Http\Message\RequestInterface;

/**
 * Placeholder route.
 */
final readonly class Placeholder implements HttpRouteInterface
{
    /**
     * @param array<string, string> $defaults
     */
    public function __construct(
        /** @var array<string, string> */
        private array $defaults,
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
        /** @var array<string, string> $defaults */
        $defaults = $options['defaults'] ?? [];
        /** @psalm-var int|null $priority */
        $priority = $options['priority'] ?? null;

        return new self($defaults, $priority);
    }

    /** @inheritDoc */
    #[Override]
    public function match(
        RequestInterface $request,
        int|null $pathOffset = null,
        array $options = []
    ): HttpRouteMatch|null {
        return new HttpRouteMatch($this->defaults);
    }

    /** @inheritDoc */
    #[Override]
    public function assemble(array $params = [], array $options = []): AssembledUrl
    {
        return new AssembledUrl();
    }

    #[Override]
    public function getPriority(): ?int
    {
        return $this->priority;
    }
}
