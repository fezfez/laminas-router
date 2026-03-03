<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\Exception;
use Laminas\Router\Http\HttpRouteMatch;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Stdlib\RequestInterface;
use Override;
use Traversable;

use function array_map;
use function explode;
use function in_array;
use function method_exists;
use function strtoupper;
use function trim;

/**
 * Method route.
 *
 * @final
 */
class Method implements HttpRouteInterface
{
    /**
     * @internal
     * @deprecated Since 3.9.0 This property will be removed or made private in version 4.0
     */
    public int|null $priority = null;

    /**
     * Create a new method route.
     */
    public function __construct(
        /**
         * Verb to match.
         */
        protected string $verb,
        /**
         * Default values.
         */
        protected array $defaults = []
    ) {
    }

    /**
     * @inheritDoc
     * @throws Exception\InvalidArgumentException
     */
    #[Override]
    public static function factory(iterable $options = []): static
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (! isset($options['verb'])) {
            throw new Exception\InvalidArgumentException('Missing "verb" in options array');
        }

        if (! isset($options['defaults'])) {
            $options['defaults'] = [];
        }

        return new static($options['verb'], $options['defaults']);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function match(RequestInterface $request, int|null $pathOffset = null, array $options = []): ?HttpRouteMatch
    {
        if (! method_exists($request, 'getMethod')) {
            return null;
        }

        $requestVerb = strtoupper((string) $request->getMethod());
        $matchVerbs  = explode(',', strtoupper($this->verb));
        $matchVerbs  = array_map(trim(...), $matchVerbs);

        if (in_array($requestVerb, $matchVerbs)) {
            return new HttpRouteMatch($this->defaults);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function assemble(array $params = [], array $options = []): string
    {
        // The request method does not contribute to the path, thus nothing is returned.
        return '';
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getAssembledParams(): array
    {
        return [];
    }
}
