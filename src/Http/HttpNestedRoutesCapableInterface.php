<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\RouteStackInterface;

/**
 * HTTP route stacks that support hierarchical route names (e.g. "parent/child")
 * during assembly.
 */
interface HttpNestedRoutesCapableInterface extends RouteStackInterface
{
}
