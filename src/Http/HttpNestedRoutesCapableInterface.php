<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\RouteStackInterface;

/**
 * HTTP route stacks that support hierarchical route names (e.g. "parent/child")
 * during assembly.
 *
 * @template TRoute of HttpRouteInterface
 * @extends RouteStackInterface<TRoute>
 */
interface HttpNestedRoutesCapableInterface extends RouteStackInterface
{
}
