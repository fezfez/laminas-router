<?php

declare(strict_types=1);

namespace Laminas\Router;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Override;
use Psr\Container\ContainerInterface;

/**
 * @internal
 *
 * @final
 */
class RouterFactory implements FactoryInterface
{
    /**
     * Create and return the router
     *
     * Delegates to the HttpRouter service.
     *
     * @param  string $name
     * @param  null|array $options
     * @return RouteStackInterface
     */
    #[Override]
    public function __invoke(ContainerInterface $container, $name, ?array $options = null)
    {
        return $container->get('HttpRouter');
    }
}
