<?php

declare(strict_types=1);

namespace LaminasTest\Router;

use Laminas\Router\Exception\InvalidArgumentException;
use Laminas\Router\PriorityList;
use PHPUnit\Framework\TestCase;

use function array_keys;

final class PriorityListTest extends TestCase
{
    private PriorityList $list;

    public function setUp(): void
    {
        $this->list = new PriorityList();
    }

    public function testInsert(): void
    {
        $this->list->insert('foo', new TestAsset\DummyRoute(), 0);

        $this->assertCount(1, $this->list->getAsArray());
        $this->assertSame(['foo'], array_keys([...$this->list->getAsArray()]));
    }

    public function testInsertDuplicateRouteThrowsException(): void
    {
        $this->list->insert('foo', new TestAsset\DummyRoute(), 0);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Route with name "foo" already exists');
        $this->list->insert('foo', new TestAsset\DummyRoute(), 0);
    }

    public function testInsertAfterRemoveSucceeds(): void
    {
        $route = new TestAsset\DummyRoute();

        $this->list->insert('foo', $route, 0);
        $this->list->remove('foo');
        $this->list->insert('foo', $route, 0);

        $this->assertEquals($route, $this->list->get('foo'));
    }

    public function testRemove(): void
    {
        $this->list->insert('foo', new TestAsset\DummyRoute(), 0);
        $this->list->insert('bar', new TestAsset\DummyRoute(), 0);

        $this->assertCount(2, $this->list->getAsArray());

        $this->list->remove('foo');

        $this->assertCount(1, $this->list->getAsArray());
    }

    public function testRemovingNonExistentRouteDoesNotYieldError(): void
    {
        $this->expectNotToPerformAssertions();
        $this->list->remove('foo');
    }

    public function testClear(): void
    {
        $this->list->insert('foo', new TestAsset\DummyRoute(), 0);
        $this->list->insert('bar', new TestAsset\DummyRoute(), 0);

        $this->assertCount(2, $this->list->getAsArray());

        $this->list->clear();

        $this->assertCount(0, $this->list->getAsArray());
    }

    public function testGet(): void
    {
        $route = new TestAsset\DummyRoute();

        $this->list->insert('foo', $route, 0);

        $this->assertEquals($route, $this->list->get('foo'));
        $this->assertNull($this->list->get('bar'));
    }

    public function testLIFOOnly(): void
    {
        $this->list->insert('foo', new TestAsset\DummyRoute(), 0);
        $this->list->insert('bar', new TestAsset\DummyRoute(), 0);
        $this->list->insert('baz', new TestAsset\DummyRoute(), 0);

        $this->assertEquals(['baz', 'bar', 'foo'], array_keys([...$this->list->getAsArray()]));
    }

    public function testPriorityOnly(): void
    {
        $this->list->insert('foo', new TestAsset\DummyRoute(), 1);
        $this->list->insert('bar', new TestAsset\DummyRoute(), 0);
        $this->list->insert('baz', new TestAsset\DummyRoute(), 2);

        $this->assertEquals(['baz', 'foo', 'bar'], array_keys([...$this->list->getAsArray()]));
    }

    public function testLIFOWithPriority(): void
    {
        $this->list->insert('foo', new TestAsset\DummyRoute(), 0);
        $this->list->insert('bar', new TestAsset\DummyRoute(), 0);
        $this->list->insert('baz', new TestAsset\DummyRoute(), 1);

        $this->assertEquals(['baz', 'bar', 'foo'], array_keys([...$this->list->getAsArray()]));
    }

    public function testPriorityWithNegativesAndNull(): void
    {
        $this->list->insert('foo', new TestAsset\DummyRoute(), 0);
        $this->list->insert('bar', new TestAsset\DummyRoute(), 1);
        $this->list->insert('baz', new TestAsset\DummyRoute(), -1);

        $this->assertEquals(['bar', 'foo', 'baz'], array_keys([...$this->list->getAsArray()]));
    }
}
