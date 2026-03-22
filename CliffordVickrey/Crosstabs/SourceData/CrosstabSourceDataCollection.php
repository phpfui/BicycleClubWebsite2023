<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\SourceData;

use ArrayIterator;
use CliffordVickrey\Crosstabs\Exception\CrosstabUnexpectedValueException;
use CliffordVickrey\Crosstabs\Utilities\CrosstabExtractionUtilities;
use Countable;
use IteratorAggregate;
use Traversable;

use function array_combine;
use function array_keys;
use function array_map;
use function count;
use function is_iterable;
use function is_object;
use function iterator_to_array;
use function sprintf;

/**
 * @implements IteratorAggregate<int, CrosstabSourceDataRow>
 * @internal Used to parse raw data in a somewhat structured way
 */
final readonly class CrosstabSourceDataCollection implements Countable, IteratorAggregate
{
    /**
     * @param list<CrosstabSourceDataRow> $rows
     */
    public function __construct(private array $rows)
    {
    }

    /**
     * @param iterable<array-key, mixed> $rawData
     * @param non-empty-string|null $keyN
     * @param non-empty-string|null $keyWeightedN
     * @return self
     * @psalm-suppress MixedAssignment
     */
    public static function fromRawData(iterable $rawData, ?string $keyN = null, ?string $keyWeightedN = null): self
    {
        $rows = [];

        foreach ($rawData as $rawRow) {
            $rows[] = self::parseRawRow($rawRow, $keyN, $keyWeightedN);
        }

        return new self($rows);
    }

    /**
     * @param mixed $rawRow
     * @param non-empty-string|null $keyN
     * @param non-empty-string|null $keyWeightedN
     * @return CrosstabSourceDataRow
     */
    private static function parseRawRow(
        mixed $rawRow,
        ?string $keyN = null,
        ?string $keyWeightedN = null
    ): CrosstabSourceDataRow {
        if ($rawRow instanceof CrosstabSourceDataRow) {
            return clone $rawRow;
        }

        if (is_object($rawRow) && !is_iterable($rawRow)) {
            $rawRow = (array)$rawRow;
        }

        if (!is_iterable($rawRow)) {
            throw CrosstabUnexpectedValueException::fromValue(
                $rawRow,
                sprintf('object, array, or %s', Traversable::class)
            );
        }

        if ($rawRow instanceof Traversable) {
            $rowArray = iterator_to_array($rawRow);
        } else {
            $rowArray = $rawRow;
        }

        $n = 1;

        if (null !== $keyN) {
            $n = CrosstabExtractionUtilities::extractNumeric($keyN, $rowArray) ?? 1;
        }

        $weightedN = null;

        if (null !== $keyWeightedN) {
            $weightedN = CrosstabExtractionUtilities::extractNumeric($keyWeightedN, $rowArray);
        }

        $rowArray = array_combine(array_map(strval(...), array_keys($rowArray)), $rowArray);

        return new CrosstabSourceDataRow($rowArray, $n, $weightedN);
    }

    /**
     * @return ArrayIterator<positive-int, CrosstabSourceDataRow>
     */
    public function getIterator(): ArrayIterator
    {
        /** @var ArrayIterator<positive-int, CrosstabSourceDataRow> $it */
        $it = new ArrayIterator($this->rows);
        return $it;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->rows);
    }
}
