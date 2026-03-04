<?php

declare(strict_types=1);

namespace Laminas\Router\Http\RouteDefinition;

use Laminas\Router\Exception;

use function array_pop;
use function count;

/**
 * @internal
 */
final class RouteDefinition
{
    /** @var list<RouteDefinitionPartInterface>  */
    private array $parts = [];
    /** @var array<array-key, list<RouteDefinitionPartInterface>> */
    private array $optional = [];

    public function addPart(RouteDefinitionPartInterface $part): void
    {
        if (count($this->optional)) {
            $this->optional[count($this->optional) - 1][] = $part;

            return;
        }

        $this->parts[] = $part;
    }

    public function startOptional(): void
    {
        $this->optional[] = [];
    }

    public function endOptional(): void
    {
        if (count($this->optional) === 0) {
            throw new Exception\RuntimeException('Found closing bracket without matching opening bracket');
        }

        $current = array_pop($this->optional);

        $this->addPart(new RouteDefinitionOption(...$current));
    }

    /** @return list<RouteDefinitionPartInterface> */
    public function getParts(): array
    {
        if (count($this->optional) !== 0) {
            throw new Exception\RuntimeException('Found unbalanced brackets');
        }

        return $this->parts;
    }
}
