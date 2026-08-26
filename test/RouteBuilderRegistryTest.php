<?php

declare(strict_types=1);

namespace LaminasTest\Router;

use Laminas\Router\Exception\RuntimeException;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\LiteralBuilder;
use Laminas\Router\Http\TreeRouteStack;
use Laminas\Router\RouteBuilderInterface;
use Laminas\Router\RouteInterface;
use LaminasTest\Router\TestAsset\RouteBuilderRegistryFactory;
use PHPUnit\Framework\TestCase;

final class RouteBuilderRegistryTest extends TestCase
{
    public function testUnknownTypeThrows(): void
    {
        $registry = RouteBuilderRegistryFactory::withDefaults();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to resolve route builder for type "foo"');
        $registry->build('foo', ['name' => 'foo']);
    }

    public function testBuildsLiteralViaAlias(): void
    {
        $registry = RouteBuilderRegistryFactory::withDefaults();
        $route    = $registry->build('literal', [
            'name'  => 'home',
            'route' => '/',
        ]);

        $this->assertInstanceOf(Literal::class, $route);
    }

    public function testExtraBuildersTakePrecedence(): void
    {
        $extra = new /** @implements RouteBuilderInterface<Literal> */ class implements RouteBuilderInterface {
            public function build(array $options = []): RouteInterface
            {
                return new Literal('extra', '/extra');
            }
        };

        $registry = RouteBuilderRegistryFactory::withDefaults([
            'custom' => $extra,
        ]);

        $route = $registry->build('custom');
        $this->assertInstanceOf(Literal::class, $route);
        $this->assertSame('/extra', $route->assemble()->path);
    }

    public function testGetReturnsSameBuilderInstanceOnRepeatedLookup(): void
    {
        $registry = RouteBuilderRegistryFactory::withDefaults();
        $first    = $registry->get(Literal::class);
        $second   = $registry->get(Literal::class);

        $this->assertSame($first, $second);
        $this->assertInstanceOf(LiteralBuilder::class, $first);
    }

    public function testCompositeCycleBuildsPartWithChildren(): void
    {
        $registry = RouteBuilderRegistryFactory::withDefaults();
        $stack    = $registry->build(TreeRouteStack::class, [
            'routes' => [
                'foo' => [
                    'type'          => 'literal',
                    'options'       => [
                        'route' => '/foo',
                    ],
                    'may_terminate' => true,
                    'child_routes'  => [
                        'bar' => [
                            'type'    => 'literal',
                            'options' => [
                                'route' => '/bar',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertInstanceOf(TreeRouteStack::class, $stack);
        $this->assertTrue($stack->hasRoute('foo'));
    }
}
