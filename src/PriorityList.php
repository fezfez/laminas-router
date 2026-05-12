<?php

declare(strict_types=1);

namespace Laminas\Router;

use Laminas\Stdlib\PriorityList as StdlibPriorityList;

/**
 * @internal
 *
 * @psalm-internal \Laminas\Router
 * @psalm-internal \LaminasTest\Router
 *
 * @template TValue of RouteInterface
 * @template-extends StdlibPriorityList<string, TValue>
 */
final class PriorityList extends StdlibPriorityList
{
}
