<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\AssembledUrl;
use Laminas\Router\Exception;
use Laminas\Router\Http\HttpRouteMatch;
use Laminas\Router\RouteMatchInterface;
use Override;
use Psr\Http\Message\RequestInterface;

use function array_map;
use function explode;
use function in_array;
use function is_string;
use function strtoupper;
use function trim;

/**
 * Method route.
 */
final readonly class Method implements HttpRouteInterface
{
    /**
     * Create a new method route.
     *
     * @param array<string, string|int|float|null> $defaults
     */
    public function __construct(
        private string $name,
        /**
         * Verb to match.
         */
        private string $verb,
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
        $name = $options['name'] ?? null;
        /** @var mixed $verb */
        $verb = $options['verb'] ?? null;
        /** @psalm-var array<string, string|int|float|null>  $defaults */
        $defaults = $options['defaults'] ?? [];
        /** @psalm-var int|null $priority */
        $priority = $options['priority'] ?? null;

        if (! is_string($verb)) {
            throw new Exception\InvalidArgumentException('Missing "verb" in options array');
        }

        if (! is_string($name)) {
            throw new Exception\InvalidArgumentException('Missing "name" in options array');
        }

        return new self($name, $verb, $defaults, $priority);
    }

    /** @inheritDoc */
    #[Override]
    public function match(
        RequestInterface $request,
        int|null $pathOffset = null,
        array $options = []
    ): ?RouteMatchInterface {
        $requestVerb = strtoupper($request->getMethod());
        $matchVerbs  = explode(',', strtoupper($this->verb));
        $matchVerbs  = array_map(trim(...), $matchVerbs);

        if (in_array($requestVerb, $matchVerbs)) {
            return new HttpRouteMatch($this->defaults, $this->name, 0);
        }

        return null;
    }

    /** @inheritDoc */
    #[Override]
    public function assemble(array $params = [], array $options = []): AssembledUrl
    {
        // The request method does not contribute to the path, thus nothing is returned.
        return new AssembledUrl();
    }

    #[Override]
    public function getPriority(): ?int
    {
        return $this->priority;
    }
}
