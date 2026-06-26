<?php

declare(strict_types=1);

namespace Laminas\Router;

use Laminas\Router\Exception\InvalidArgumentException;

use function sprintf;
use function uasort;

/**
 * @internal
 *
 * @psalm-internal \Laminas\Router
 * @psalm-internal \LaminasTest\Router
 *
 * @template TValue of RouteInterface
 */
final class PriorityList
{
    /** @var array<non-empty-string, array{data: TValue, priority: int, serial: positive-int|0}> */
    private array $items = [];

    /** @var positive-int|0 */
    private int $serial = 0;

    private bool $sorted = false;

    /**
     * @param non-empty-string $name
     * @param TValue $value
     * @throws InvalidArgumentException
     */
    public function insert(string $name, RouteInterface $value, int|null $priority): void
    {
        if (isset($this->items[$name])) {
            throw new InvalidArgumentException(sprintf('Route with name "%s" already exists', $name));
        }

        $this->sorted = false;

        $routePriority = $value->getPriority();

        if ($priority === null && $routePriority !== null) {
            $priority = $routePriority;
        }

        $this->items[$name] = [
            'data'     => $value,
            'priority' => $priority ?? 0,
            'serial'   => $this->serial++,
        ];
    }

    /** @param non-empty-string $name */
    public function remove(string $name): void
    {
        unset($this->items[$name]);
    }

    public function clear(): void
    {
        $this->items  = [];
        $this->serial = 0;
        $this->sorted = false;
    }

    /**
     * @param non-empty-string $name
     * @return TValue|null
     */
    public function get(string $name): RouteInterface|null
    {
        if (! isset($this->items[$name])) {
            return null;
        }

        return $this->items[$name]['data'];
    }

    private function sort(): void
    {
        if (! $this->sorted) {
            uasort(
                $this->items,
                static fn(array $a, array $b): int => $b['priority'] <=> $a['priority'] ?: $b['serial'] <=> $a['serial']
            );
            $this->sorted = true;
        }
    }

    /** @return array<non-empty-string, TValue> */
    public function getAsArray(): array
    {
        $this->sort();
        $result = [];
        foreach ($this->items as $name => $item) {
            $result[$name] = $item['data'];
        }
        return $result;
    }
}
