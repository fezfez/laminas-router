<?php

declare(strict_types=1);

namespace LaminasTest\Router\Http;

use Laminas\Router\Http\HttpRouteMatch;
use PHPUnit\Framework\TestCase;

final class RouteMatchTest extends TestCase
{
    public function testParamsAreStored(): void
    {
        $match = new HttpRouteMatch(['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $match->getParams());
    }

    public function testLengthIsStored(): void
    {
        $match = new HttpRouteMatch([], 10);

        $this->assertEquals(10, $match->getLength());
    }

    public function testLengthIsMerged(): void
    {
        $match = new HttpRouteMatch([], 10);
        $match = $match->merge(new HttpRouteMatch([], 5));

        $this->assertEquals(15, $match->getLength());
    }

    public function testMatchedRouteNameIsSet(): void
    {
        $match = new HttpRouteMatch([]);
        $match = $match->setMatchedRouteName('foo');

        $this->assertEquals('foo', $match->getMatchedRouteName());
    }

    public function testMatchedRouteNameIsPrependedWhenAlreadySet(): void
    {
        $match = new HttpRouteMatch([]);
        $match = $match->setMatchedRouteName('foo');
        $match = $match->setMatchedRouteName('bar');

        $this->assertEquals('bar/foo', $match->getMatchedRouteName());
    }

    public function testMatchedRouteNameIsOverriddenOnMerge(): void
    {
        $match = new HttpRouteMatch([]);
        $match = $match->setMatchedRouteName('foo');

        $subMatch = new HttpRouteMatch([]);
        $subMatch = $subMatch->setMatchedRouteName('bar');

        $match = $match->merge($subMatch);

        $this->assertEquals('bar', $match->getMatchedRouteName());
    }
}
