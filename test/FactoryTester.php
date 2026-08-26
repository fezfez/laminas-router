<?php

declare(strict_types=1);

namespace LaminasTest\Router;

use Laminas\Router\Exception\InvalidArgumentException;
use Laminas\Router\RouteBuilderInterface;
use Laminas\Router\RouteInterface;
use PHPUnit\Framework\TestCase;

/**
 * Helper to test route builders.
 */
final class FactoryTester
{
    /**
     * Test a builder.
     *
     * @param array<string, string> $requiredOptions
     * @param class-string<RouteInterface> $routeClass
     * @param array<string, mixed> $options
     */
    public function testFactory(
        RouteBuilderInterface $builder,
        string $routeClass,
        array $requiredOptions,
        array $options
    ): void {
        foreach ($requiredOptions as $option => $exceptionMessage) {
            $testOptions = $options;

            unset($testOptions[$option]);

            try {
                $builder->build($testOptions);
                TestCase::fail('An expected exception was not thrown');
            } catch (InvalidArgumentException $exception) {
                TestCase::assertStringContainsString($exceptionMessage, $exception->getMessage());
            }
        }

        TestCase::assertInstanceOf($routeClass, $builder->build($options));
    }
}
