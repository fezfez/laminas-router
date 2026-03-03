<?php

declare(strict_types=1);

namespace LaminasTest\Router;

use ArrayIterator;
use Laminas\Router\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

use function sprintf;

/**
 * Helper to test route factories.
 */
final class FactoryTester
{
    /**
     * Create a new factory tester.
     */
    public function __construct(
        /**
         * Test case to call assertions to.
         */
        protected TestCase $testCase
    ) {
    }

    /**
     * Test a factory.
     *
     * @psalm-param class-string $classname
     */
    public function testFactory(string $classname, array $requiredOptions, array $options): void
    {
        $testCase = $this->testCase; // hack for phpcs ...
        $factory  = sprintf('%s::factory', $classname);

        // Test required options.
        foreach ($requiredOptions as $option => $exceptionMessage) {
            $testOptions = $options;

            unset($testOptions[$option]);

            try {
                $factory($testOptions);
                $testCase::fail('An expected exception was not thrown');
            } catch (InvalidArgumentException $e) {
                $testCase::assertStringContainsString($exceptionMessage, $e->getMessage());
            }
        }

        // Create the route, will throw an exception if something goes wrong.
        $testCase::assertInstanceOf($classname, $factory($options));

        // Try the same with an iterator.
        $testCase::assertInstanceOf($classname, $factory(new ArrayIterator($options)));
    }
}
