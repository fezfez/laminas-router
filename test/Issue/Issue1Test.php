<?php

declare(strict_types=1);

namespace LaminasTest\Router\Issue;

use Laminas\Diactoros\Request;
use Laminas\Diactoros\Uri;
use Laminas\Router\Http\Segment;
use Laminas\Router\Http\TreeRouteStack;
use Laminas\Router\RoutePluginManager;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;

final class Issue1Test extends TestCase
{
    private function createRouter(array $segmentOptions = []): TreeRouteStack
    {
        return TreeRouteStack::factory([
            'route_plugins' => new RoutePluginManager(new ServiceManager()),
            'routes'        => [
                'example-route' => [
                    'type'    => Segment::class,
                    'options' => [
                        'route' => '/example/route/with/:token',
                        ...$segmentOptions,
                    ],
                ],
            ],
        ]);
    }

    public function testAssembleEncodesSegmentByDefault(): void
    {
        $router = $this->createRouter();

        $this->assertSame(
            '/example/route/with/token%2Fwith%2Fslashes',
            $router->assemble(['token' => 'token/with/slashes'], ['name' => 'example-route'])->toString()
        );
    }

    public function testMatchDecodesSegmentByDefault(): void
    {
        $router  = $this->createRouter();
        $request = (new Request())->withUri(new Uri('http://example.com/example/route/with/token%2Fwith%2Fslashes'));

        $match = $router->match($request);

        $this->assertNotNull($match);
        $this->assertSame('token/with/slashes', $match->getParam('token'));
    }

    public function testAssembleDoesNotEncodeSegmentWhenDisabled(): void
    {
        $router = $this->createRouter([
            'disable_segment_encoding' => [
                'token' => true,
            ],
        ]);

        $this->assertSame(
            '/example/route/with/token/with/slashes',
            $router->assemble(['token' => 'token/with/slashes'], ['name' => 'example-route'])->toString()
        );
    }

    public function testMatchDoesNotDecodeSegmentWhenDisabled(): void
    {
        $router  = $this->createRouter([
            'disable_segment_encoding' => [
                'token' => true,
            ],
        ]);
        $request = (new Request())->withUri(new Uri('http://example.com/example/route/with/token%2Fwith%2Fslashes'));

        $match = $router->match($request);

        $this->assertNotNull($match);
        $this->assertSame('token%2Fwith%2Fslashes', $match->getParam('token'));
    }

    /**
     * Explicitly setting the option to `false` must behave identically to
     * omitting it: encoding stays enabled.
     */
    public function testAssembleEncodesSegmentWhenExplicitlyEnabled(): void
    {
        $router = $this->createRouter([
            'disable_segment_encoding' => [
                'token' => false,
            ],
        ]);

        $this->assertSame(
            '/example/route/with/token%2Fwith%2Fslashes',
            $router->assemble(['token' => 'token/with/slashes'], ['name' => 'example-route'])->toString()
        );
    }
}
