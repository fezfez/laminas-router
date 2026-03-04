<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\Exception;
use Laminas\Router\Http\HttpRouteMatch;
use Laminas\Stdlib\RequestInterface;
use Laminas\Uri\Http;
use Override;

use function is_string;
use function method_exists;

/**
 * Scheme route.
 */
final class Scheme implements HttpRouteInterface
{
    /**
     * @internal
     * @deprecated Since 3.9.0 This property will be removed or made private in version 4.0
     */
    public int|null $priority = null;

    /**
     * Create a new scheme route.
     *
     * @param array<string, string> $defaults
     */
    public function __construct(
        /**
         * Scheme to match.
         */
        private readonly string $scheme,
        /**
         * Default values.
         */
        private readonly array $defaults = []
    ) {
    }

    /**
     * @param array{'scheme'?:string, 'defaults'?: array<string, string>} $options
     */
    #[Override]
    public static function factory(array $options = []): static
    {
        $scheme   = $options['scheme'] ?? null;
        $defaults = $options['defaults'] ?? [];

        if (! is_string($scheme) || $scheme === '') {
            throw new Exception\InvalidArgumentException('Missing "scheme" in options array');
        }

        return new self($scheme, $defaults);
    }

    /** @inheritDoc */
    #[Override]
    public function match(RequestInterface $request, int|null $pathOffset = null, array $options = []): ?HttpRouteMatch
    {
        if (! method_exists($request, 'getUri')) {
            return null;
        }

        /** @var Http $uri */
        $uri    = $request->getUri();
        $scheme = $uri->getScheme();

        if ($scheme !== $this->scheme) {
            return null;
        }

        return new HttpRouteMatch($this->defaults);
    }

    /** @inheritDoc */
    #[Override]
    public function assemble(array $params = [], array $options = []): string
    {
        if (isset($options['uri']) && $options['uri'] instanceof Http) {
            $options['uri']->setScheme($this->scheme);
        }

        // A scheme does not contribute to the path, thus nothing is returned.
        return '';
    }

    /** @inheritDoc */
    #[Override]
    public function getAssembledParams(): array
    {
        return [];
    }
}
