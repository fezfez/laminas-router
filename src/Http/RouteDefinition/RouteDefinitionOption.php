<?php

declare(strict_types=1);

namespace Laminas\Router\Http\RouteDefinition;

use function array_values;

/**
 * @internal
 */
final readonly class RouteDefinitionOption implements RouteDefinitionPartInterface
{
    /** @var list<RouteDefinitionPartInterface>  */
    public array $part;
    public function __construct(RouteDefinitionPartInterface ...$part)
    {
        $this->part = array_values($part);
    }
}
