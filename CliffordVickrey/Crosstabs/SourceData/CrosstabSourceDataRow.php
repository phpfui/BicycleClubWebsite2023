<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\SourceData;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

use function count;

/**
 * @implements IteratorAggregate<string, mixed>
 * @internal Represents a row in the raw data
 */
final readonly class CrosstabSourceDataRow implements Countable, IteratorAggregate
{
    public float|int $weightedN;

    /**
     * @param array<string, mixed> $data
     * @param float|int $n
     * @param float|int|null $weightedN
     */
    public function __construct(private array $data, public float|int $n = 1, float|int|null $weightedN = null)
    {
        if (null === $weightedN) {
            $weightedN = $n;
        }

        $this->weightedN = $weightedN;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getValue(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    /**
     * @return ArrayIterator<string, mixed>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->data);
    }
}
