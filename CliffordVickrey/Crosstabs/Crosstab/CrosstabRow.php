<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\Crosstab;

use ArrayAccess;
use ArrayIterator;
use CliffordVickrey\Crosstabs\Exception\CrosstabOutOfBoundsException;
use Countable;
use IteratorAggregate;
use JsonSerializable;

use function abs;
use function array_filter;
use function array_reduce;
use function count;
use function is_array;
use function is_numeric;

/**
 * Table row in a crosstab
 * @implements ArrayAccess<int, CrosstabCell>
 * @implements IteratorAggregate<int, CrosstabCell>
 */
class CrosstabRow implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    /**
     * @param array<int, CrosstabCell> $cells
     */
    public function __construct(private array $cells = [])
    {
    }

    /**
     * @param array<array-key, mixed> $an_array
     * @return self
     */
    public static function __set_state(array $an_array): self
    {
        $self = new self();
        $self->hydrate($an_array);
        return $self;
    }

    /**
     * @param array<array-key, mixed> $arr
     * @return void
     * @psalm-suppress MixedAssignment
     */
    private function hydrate(array $arr): void
    {
        foreach ($arr as $val) {
            if ($val instanceof CrosstabCell) {
                $this->cells[] = $val;
            } elseif (is_array($val)) {
                $this->cells[] = CrosstabCell::__set_state($val);
            }
        }
    }

    /**
     * @return void
     */
    public function __clone()
    {
        foreach ($this->cells as $i => $cell) {
            $this->cells[$i] = clone $cell;
        }
    }

    /**
     * Returns FALSE if there are any data cells
     * @return bool
     */
    public function isHeader(): bool
    {
        return count(array_filter($this->cells, static fn($cell) => !$cell->isHeader)) < 1;
    }

    /**
     * @return int<0, max>
     */
    public function getWidth(): int
    {
        return abs(array_reduce(
            $this->cells,
            static fn(int $carry, CrosstabCell $cell) => $carry + $cell->colspan,
            0
        ));
    }

    /**
     * @return ArrayIterator<int, CrosstabCell>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->cells);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->cells);
    }

    /**
     * @return array<int, CrosstabCell>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return array<int, CrosstabCell>
     */
    public function toArray(): array
    {
        return $this->cells;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->cells[self::normalizeOffset($offset)]);
    }

    /**
     * @param mixed $offset
     * @return int
     */
    private static function normalizeOffset(mixed $offset): int
    {
        if (is_numeric($offset)) {
            $offset = (int)$offset;
        } else {
            $offset = -1;
        }

        return $offset;
    }

    /**
     * @param mixed $offset
     * @return CrosstabCell
     */
    public function offsetGet(mixed $offset): CrosstabCell
    {
        $cell = $this->cells[self::normalizeOffset($offset)] ?? null;

        if (null !== $cell) {
            return $cell;
        }

        throw CrosstabOutOfBoundsException::fromIllegalOffset($offset);
    }

    /**
     * @param mixed $offset
     * @param CrosstabCell $value
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (null === $offset) {
            $this->cells[] = $value;
            return;
        }

        $this->cells[self::normalizeOffset($offset)] = $value;
    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->cells[self::normalizeOffset($offset)]);
    }
}
