<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\Crosstab;

use ArrayIterator;
use CliffordVickrey\Crosstabs\Utilities\CrosstabMath;
use CliffordVickrey\Crosstabs\Utilities\CrosstabMathInterface;
use CliffordVickrey\Crosstabs\Writer\CrosstabHtmlWriter;
use CliffordVickrey\Crosstabs\Writer\CrosstabWriterInterface;
use IteratorAggregate;
use Stringable;
use Traversable;

use function array_filter;
use function array_map;
use function array_merge;
use function array_pad;
use function array_values;
use function array_walk;
use function count;
use function is_array;
use function is_object;
use function iterator_to_array;

/**
 * @implements IteratorAggregate<int, CrosstabRow>
 */
class Crosstab implements CrosstabInterface, IteratorAggregate, Stringable
{
    /** @var array<int, CrosstabRow> */
    protected array $rows;
    /** @var list<list<CrosstabDataItem>> */
    protected array $matrix;
    /** @var array<int, array<int, ?CrosstabCellIndexDto>>|null */
    private ?array $cartesianGrid = null;

    /**
     * @param array<int, CrosstabRow|array<int, CrosstabCell>> $rows
     * @param list<list<CrosstabDataItem>> $matrix
     */
    public function __construct(array $rows = [], array $matrix = [])
    {
        $this->hydrate(['rows' => $rows, 'matrix' => $matrix]);
    }

    /**
     * @param array<array-key, mixed> $data
     * @return void
     * @psalm-suppress MixedAssignment
     */
    private function hydrate(array $data): void
    {
        $rawRows = $data['rows'] ?? [];

        if (!is_array($rawRows)) {
            // @codeCoverageIgnoreStart
            $rawRows = [];
            // @codeCoverageIgnoreEnd
        }

        $rows = [];

        foreach ($rawRows as $i => $rawRow) {
            $rows[(int)$i] = self::parseRow($rawRow);
        }

        $this->rows = $rows;

        $rawMatrix = $data['matrix'] ?? [];

        if ($rawMatrix instanceof Traversable) {
            // @codeCoverageIgnoreStart
            $rawMatrix = iterator_to_array($rawMatrix);
            // @codeCoverageIgnoreEnd
        }

        if (!is_array($rawMatrix)) {
            // @codeCoverageIgnoreStart
            $rawMatrix = [];
            // @codeCoverageIgnoreEnd
        }

        $rawMatrix = array_values($rawMatrix);

        $matrix = [];

        foreach ($rawMatrix as $i => $matrixRow) {
            $matrix[$i] = self::parseMatrixRow($matrixRow);
        }

        /** @psalm-suppress PropertyTypeCoercion */
        $this->matrix = $matrix;

        $this->cartesianGrid = self::parseCartesianGrid($data['cartesianGrid'] ?? null);
    }

    /**
     * @param mixed $val
     * @return CrosstabRow
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    private static function parseRow(mixed $val): CrosstabRow
    {
        if ($val instanceof CrosstabRow) {
            return $val;
        }

        if (is_array($val)) {
            return new CrosstabRow(array_filter(array_map(
                static fn($cell) => ($cell instanceof CrosstabCell)
                    ? $cell
                    : (is_array($cell) ? CrosstabCell::__set_state($cell) : null),
                $val
            )));
        }
        // @codeCoverageIgnoreStart
        return new CrosstabRow();
        // @codeCoverageIgnoreEnd
    }

    /**
     * @param mixed $row
     * @return list<CrosstabDataItem>
     */
    private static function parseMatrixRow(mixed $row): array
    {
        if ($row instanceof Traversable) {
            // @codeCoverageIgnoreStart
            $row = iterator_to_array($row);
            // @codeCoverageIgnoreEnd
        }

        if (!is_array($row)) {
            // @codeCoverageIgnoreStart
            return [];
            // @codeCoverageIgnoreEnd
        }

        return array_values(array_map(self::parseDataItem(...), $row));
    }

    /**
     * @param mixed $rawGrid
     * @return array<int, array<int, ?CrosstabCellIndexDto>>|null
     */
    private static function parseCartesianGrid(mixed $rawGrid): ?array
    {
        if (!is_array($rawGrid)) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        $grid = [];
        $y = -1;

        foreach ($rawGrid as $cellIndexes) {
            if (!is_array($cellIndexes)) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }

            $grid[++$y] = array_values(array_filter(
                $cellIndexes,
                static fn($val) => ($val instanceof CrosstabCellIndexDto) || null === $val
            ));
        }

        return $grid;
    }

    /**
     * @param non-empty-string $message
     * @param non-empty-string|null $title
     * @return self
     */
    public static function withoutData(string $message, ?string $title = null): self
    {
        $rows = [];

        if (null !== $title) {
            $rows[] = [CrosstabCell::header($title)];
        }

        $rows[] = [CrosstabCell::dataCell($message)];

        return new self($rows);
    }

    /**
     * @param mixed $val
     * @return CrosstabDataItem
     */
    private static function parseDataItem(mixed $val): CrosstabDataItem
    {
        if ($val instanceof CrosstabDataItem) {
            return $val;
        }

        // @codeCoverageIgnoreStart
        if (is_object($val)) {
            $val = (array)$val;
        }

        if (is_array($val)) {
            return CrosstabDataItem::__set_state($val);
        }

        return CrosstabDataItem::__set_state([]);
        // @codeCoverageIgnoreEnd
    }

    /**
     * @return void
     */
    public function clearState(): void
    {
        $this->cartesianGrid = null;
    }

    /**
     * Gets a cell at specified Cartesian coordinates. If none exists, return NULL
     * @param int $x
     * @param int $y
     * @return CrosstabCell|null
     */
    public function getCell(int $x, int $y): ?CrosstabCell
    {
        $grid = $this->getCartesianGrid();

        $dto = $grid[$y][$x] ?? null;

        if (null === $dto) {
            return null;
        }

        return $this->rows[$dto->rowIndex][$dto->cellIndex];
    }

    /**
     * @return array<int, array<int, ?CrosstabCellIndexDto>>
     */
    protected function getCartesianGrid(): array
    {
        if (null === $this->cartesianGrid) {
            $this->cartesianGrid = $this->buildCartesianGrid();
        }

        return $this->cartesianGrid;
    }

    /**
     * Maps X and Y Cartesian coordinates to row and cell indexes (handling cells that span multiple rows and/or
     * columns)
     * @return array<int, array<int, ?CrosstabCellIndexDto>>
     */
    protected function buildCartesianGrid(): array
    {
        $currentY = 0;
        $maxX = 0;
        $grid = [];
        $rowspanLookAhead = [];

        foreach ($this->rows as $i => $row) {
            $grid[$currentY] = [];

            $xCount = count($row);

            if ($xCount < 1) {
                array_walk($rowspanLookAhead, static fn(int &$count) => $count--);
            } elseif ($xCount > $maxX) {
                $maxX = $xCount;
            }

            $currentX = 0;

            foreach ($row as $ii => $cell) {
                $valid = false;

                while (!$valid) {
                    $lookAhead = 0;

                    if (!isset($rowspanLookAhead[$currentX])) {
                        $valid = true;
                    } else {
                        /** @var int $lookAhead */
                        $lookAhead = $rowspanLookAhead[$currentX];
                        $valid = $lookAhead < 1;
                    }

                    if (!$valid) {
                        $grid[$currentY][$currentX] = null;
                        $rowspanLookAhead[$currentX] = $lookAhead - 1;
                        $currentX++;
                    }
                }

                if ($cell->rowspan > 1) {
                    for ($iii = 0; $iii < $cell->colspan; $iii++) {
                        $nextX = $currentX + $iii;
                        $grid[$currentY][$nextX] = null;
                        $rowspanLookAhead[$nextX] = $cell->rowspan - 1;
                    }
                }

                $grid[$currentY][$currentX] = new CrosstabCellIndexDto($i, $ii);

                $currentX += $cell->colspan;
            }

            $currentY++;
        }

        return array_map(static fn(array $row) => array_pad($row, $maxX, null), $grid);
    }

    /**
     * @return void
     */
    public function __clone()
    {
        foreach ($this->rows as $i => $row) {
            $this->rows[$i] = clone $row;
        }

        foreach ($this->matrix as $y => $matrixRow) {
            foreach ($matrixRow as $x => $dataItem) {
                /** @psalm-suppress PropertyTypeCoercion */
                $this->matrix[$y][$x] = clone $dataItem;
            }
        }

        if (null === $this->cartesianGrid) {
            return;
        }

        foreach ($this->cartesianGrid as $y => $gridRow) {
            foreach ($gridRow as $x => $DTO) {
                if (null === $DTO) {
                    continue;
                }

                $this->cartesianGrid[$y][$x] = clone $DTO;
            }
        }
    }

    /**
     * Gets a rectangular matrix of value objects, representing data within the crosstab
     * @return list<list<CrosstabDataItem>>
     */
    public function getMatrix(): array
    {
        return $this->matrix;
    }

    /**
     * Gets the number of independent values used to compute a chi-squared test statistic, etc.
     * @return int
     */
    public function getDegreesOfFreedom(): int
    {
        return (count($this->matrix) - 1) * (count($this->matrix[0] ?? []) - 1);
    }

    /**
     * Gets the chi-squared test statistic. The higher the value, and the lower the number of cells used as factors to
     * compute the statistic, the more likely there is to be a relationship between the row and column variables'
     * population parameters
     * @param bool $weighted Whether to use weighted values
     * @param int<1, max>|null $scale Floating-point scale to use
     * @return float
     */
    public function getChiSquared(bool $weighted = false, ?int $scale = null): float
    {
        if (null === $scale) {
            $scale = CrosstabMathInterface::DEFAULT_SCALE;
        }

        $chiSquared = 0.0;

        $math = new CrosstabMath();

        foreach ($this->matrix as $dataItems) {
            foreach ($dataItems as $dataItem) {
                $n = $weighted ? $dataItem->weightedFrequency : $dataItem->frequency;
                $expected = $weighted ? $dataItem->weightedExpectedFrequency : $dataItem->expectedFrequency;

                $difference = $math->subtract($n, $expected, $scale);
                $differenceSquared = $math->pow($difference, 2, $scale);
                $quantity = $math->divide($differenceSquared, $expected, $scale);
                $chiSquared = $math->add($chiSquared, $quantity, $scale);
            }
        }

        return $chiSquared;
    }

    /**
     * @return ArrayIterator<int, CrosstabRow>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->rows);
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->rows);
    }

    /**
     * @return array{
     *     rows: array<int, CrosstabRow>,
     *     matrix: list<list<CrosstabDataItem>>,
     *     cartesianGrid: array<int, array<int, ?CrosstabCellIndexDto>>
     * }
     */
    public function __serialize(): array
    {
        return array_merge($this->toArray(), [
            'cartesianGrid' => $this->getCartesianGrid()
        ]);
    }

    /**
     * @return array{rows: array<int, CrosstabRow>, matrix: list<list<CrosstabDataItem>>}
     */
    public function toArray(): array
    {
        return ['rows' => $this->rows, 'matrix' => $this->matrix];
    }

    /**
     * @param array<string, mixed> $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->hydrate($data);
    }

    /**
     * @return array{rows: array<int, CrosstabRow>, matrix: list<list<CrosstabDataItem>>}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @inheritDoc
     */
    public function writeToFile(
        ?string $filename = null,
        array $options = [],
        ?CrosstabWriterInterface $writer = null
    ): string {
        if (null === $writer) {
            $writer = new CrosstabHtmlWriter();
        }

        return $writer->writeToFile($this, $filename, $options);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->write();
    }

    /**
     * @inheritDoc
     */
    public function write(array $options = [], ?CrosstabWriterInterface $writer = null): string
    {
        if (null === $writer) {
            $writer = new CrosstabHtmlWriter();
        }

        return $writer->write($this, $options);
    }
}
