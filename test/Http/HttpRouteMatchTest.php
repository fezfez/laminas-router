<?php

declare(strict_types=1);

namespace LaminasTest\Router\Http;

use Laminas\Router\Http\HttpRouteMatch;
use PHPUnit\Framework\TestCase;

final class HttpRouteMatchTest extends TestCase
{
    public function testParamsAreStored(): void
    {
        $match = new HttpRouteMatch(['foo' => 'bar'], 'foo');

        $this->assertEquals(['foo' => 'bar'], $match->getParams());
    }

    public function testMatchedRouteNameIsSet(): void
    {
        $match = new HttpRouteMatch([], 'foo');

        $this->assertEquals('foo', $match->getMatchedRouteName());
    }

    public function testGetParam(): void
    {
        $match = new HttpRouteMatch(['foo' => 'bar'], 'foo');

        $this->assertEquals('bar', $match->getParam('foo'));
    }

    public function testGetNonExistentParamWithoutDefault(): void
    {
        $match = new HttpRouteMatch([], 'foo');

        $this->assertNull($match->getParam('foo'));
    }

    public function testGetNonExistentParamWithDefault(): void
    {
        $match = new HttpRouteMatch([], 'foo');

        $this->assertEquals('bar', $match->getParam('foo', 'bar'));
    }
}
