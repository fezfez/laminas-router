<?php // phpcs:disable Squiz.Commenting.FunctionComment.InvalidNoReturn


declare(strict_types=1);

namespace LaminasTest\Router\TestAsset;

use Laminas\Router\RouteInterface;
use Laminas\Router\RouteStackInterface;
use Laminas\Stdlib\RequestInterface;

/**
 * @template TRoute of RouteInterface
 * @template-implements RouteStackInterface<TRoute>
 */
final class Router implements RouteStackInterface
{
    /**
     * @inheritDoc
     */
    public static function factory(iterable $options = []): static
    {
        return new static();
    }

    /**
     * @inheritDoc
     */
    public function match(RequestInterface $request): null
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function assemble(array $params = [], array $options = []): null
    {
        return null;
    }

    /** @inheritDoc */
    public function addRoute(string $name, iterable|RouteInterface $route, ?int $priority = null): static
    {
        return $this;
    }

    /** @inheritDoc */
    public function addRoutes(iterable $routes): static
    {
        return $this;
    }

    /** @inheritDoc */
    public function removeRoute(string $name): static
    {
        return $this;
    }

    /** @inheritDoc */
    public function setRoutes(iterable $routes): static
    {
        return $this;
    }
}
