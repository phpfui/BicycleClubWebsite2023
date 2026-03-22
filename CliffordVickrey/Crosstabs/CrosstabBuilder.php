<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs;

use CliffordVickrey\Crosstabs\Crosstab\Crosstab;
use CliffordVickrey\Crosstabs\Crosstab\CrosstabCell;
use CliffordVickrey\Crosstabs\Crosstab\CrosstabDataItem;
use CliffordVickrey\Crosstabs\Exception\CrosstabInvalidArgumentException;
use CliffordVickrey\Crosstabs\Helper\CrosstabTabulator;
use CliffordVickrey\Crosstabs\Helper\CrosstabTabulatorInterface;
use CliffordVickrey\Crosstabs\Helper\CrosstabTreeBuilder;
use CliffordVickrey\Crosstabs\Helper\CrosstabTreeBuilderInterface;
use CliffordVickrey\Crosstabs\Helper\CrosstabVariableCollector;
use CliffordVickrey\Crosstabs\Helper\CrosstabVariableCollectorInterface;
use CliffordVickrey\Crosstabs\NumberFormatting\CrosstabNumberFormatterFlyweight;
use CliffordVickrey\Crosstabs\NumberFormatting\CrosstabNumberFormatterFlyweightInterface;
use CliffordVickrey\Crosstabs\NumberFormatting\CrosstabNumberFormatterType;
use CliffordVickrey\Crosstabs\Options\CrosstabOptions;
use CliffordVickrey\Crosstabs\Options\CrosstabVariable;
use CliffordVickrey\Crosstabs\Options\CrosstabVariableCollection;
use CliffordVickrey\Crosstabs\SourceData\CrosstabSourceDataCollection;
use CliffordVickrey\Crosstabs\Tree\CrosstabTree;
use CliffordVickrey\Crosstabs\Tree\CrosstabTreeCategoryPayload;
use CliffordVickrey\Crosstabs\Utilities\CrosstabCastingUtilities;
use CliffordVickrey\Crosstabs\Utilities\CrosstabExtractionUtilities;
use CliffordVickrey\Crosstabs\Utilities\CrosstabMathUtilities;

use function array_filter;
use function array_intersect_key;
use function array_key_last;
use function array_keys;
use function count;
use function in_array;
use function is_int;
use function max;

class CrosstabBuilder extends CrosstabOptions implements CrosstabBuilderInterface
{
    private readonly CrosstabNumberFormatterFlyweightInterface $formatterFlyweight;
    private readonly CrosstabTabulatorInterface $tabulator;
    private readonly CrosstabTreeBuilderInterface $treeBuilder;
    private readonly CrosstabVariableCollectorInterface $variableCollector;

    /**
     * @param CrosstabNumberFormatterFlyweightInterface|null $formatterFlyweight
     * @param CrosstabTabulatorInterface|null $tabulator
     * @param CrosstabTreeBuilderInterface|null $treeBuilder
     * @param CrosstabVariableCollectorInterface|null $variableCollector
     */
    public function __construct(
        ?CrosstabNumberFormatterFlyweightInterface $formatterFlyweight = null,
        ?CrosstabTabulatorInterface $tabulator = null,
        ?CrosstabTreeBuilderInterface $treeBuilder = null,
        ?CrosstabVariableCollectorInterface $variableCollector = null
    ) {
        $this->formatterFlyweight = $formatterFlyweight ?? new CrosstabNumberFormatterFlyweight();
        $this->tabulator = $tabulator ?? new CrosstabTabulator();
        $this->treeBuilder = $treeBuilder ?? new CrosstabTreeBuilder();
        $this->variableCollector = $variableCollector ?? new CrosstabVariableCollector();
    }

    /**
     * @inheritDoc
     */
    public function build(): Crosstab
    {
        $this->assertValidOptions();

        $sourceData = CrosstabSourceDataCollection::fromRawData(
            $this->rawData,
            $this->keyFrequency,
            $this->keyWeightedFrequency
        );

        if (0 === count($sourceData)) {
            return $this->buildEmptyCrossTab();
        }

        $variables = $this->collectVariables($sourceData);

        if (!$variables->isValid()) {
            return $this->buildEmptyCrossTab();
        }

        $totals = $this->tabulator->tabulate($variables, $sourceData);

        $tree = $this->buildTree($variables, $totals);

        if (count($variables) < 2) {
            return $this->buildSimple($tree);
        }

        return $this->buildComplex($tree);
    }

    /**
     * Throws exception if builder is in an invalid state
     * @return void
     */
    private function assertValidOptions(): void
    {
        if (null === $this->rowVariableName) {
            throw new CrosstabInvalidArgumentException('No row variable provided');
        }

        if (0 === count($this->getDataHeaders())) {
            throw new CrosstabInvalidArgumentException('No frequency or percent to display in table');
        }
    }

    /**
     * Gets the data headers (frequency, percentage, etc.) that will appear in the table
     * @return array<string, string>
     */
    private function getDataHeaders(): array
    {
        $headers = [
            CrosstabDataItem::FREQUENCY => $this->messageFrequency,
            CrosstabDataItem::PERCENT => $this->messagePercent,
            CrosstabDataItem::EXPECTED_FREQUENCY => $this->messageExpectedFrequency,
            CrosstabDataItem::EXPECTED_PERCENT => $this->messageExpectedPercent,
            CrosstabDataItem::WEIGHTED_PERCENT => $this->messageWeightedPercent,
            CrosstabDataItem::WEIGHTED_FREQUENCY => $this->messageWeightedFrequency,
            CrosstabDataItem::WEIGHTED_EXPECTED_FREQUENCY => $this->messageWeightedExpectedFrequency,
            CrosstabDataItem::WEIGHTED_EXPECTED_PERCENT => $this->messageWeightedExpectedPercent
        ];

        $headersToShow = array_filter([
            CrosstabDataItem::EXPECTED_FREQUENCY => $this->showExpectedFrequency,
            CrosstabDataItem::EXPECTED_PERCENT => $this->showExpectedPercent,
            CrosstabDataItem::FREQUENCY => $this->showFrequency,
            CrosstabDataItem::PERCENT => $this->showPercent,
            CrosstabDataItem::WEIGHTED_EXPECTED_FREQUENCY => $this->showWeightedExpectedFrequency,
            CrosstabDataItem::WEIGHTED_EXPECTED_PERCENT => $this->showWeightedExpectedPercent,
            CrosstabDataItem::WEIGHTED_PERCENT => $this->showWeightedPercent,
            CrosstabDataItem::WEIGHTED_FREQUENCY => $this->showWeightedFrequency
        ]);

        return array_intersect_key($headers, $headersToShow);
    }

    /**
     * No data? Return an empty table
     * @return Crosstab
     */
    private function buildEmptyCrossTab(): Crosstab
    {
        return Crosstab::withoutData($this->messageNoData, $this->title);
    }

    /**
     * Gets a collection of layer, row, and column variables, each with a complete list of categories that belong to the
     * dataset
     * @param CrosstabSourceDataCollection $sourceData
     * @return CrosstabVariableCollection
     */
    private function collectVariables(CrosstabSourceDataCollection $sourceData): CrosstabVariableCollection
    {
        $rowVarData = [
            'name' => $this->rowVariableName,
            'description' => $this->rowVariableDescription,
            'categories' => $this->rowVariableCategories
        ];

        $colVarData = [];

        if (null !== $this->colVariableName) {
            $colVarData = [
                'name' => $this->colVariableName,
                'description' => $this->colVariableDescription,
                'categories' => $this->colVariableCategories
            ];
        }

        return $this->variableCollector->collectVariables(
            $sourceData,
            $rowVarData,
            $colVarData,
            $this->layers
        );
    }

    /**
     * @param CrosstabVariableCollection $variables
     * @param array{n: array<string, float>, weightedN: array<string, float>} $totals
     * @return CrosstabTree
     */
    private function buildTree(
        CrosstabVariableCollection $variables,
        array $totals
    ): CrosstabTree {
        return $this->treeBuilder->buildTree(
            $variables,
            $totals,
            $this->percentType,
            $this->messageTotal,
            $this->mathematicalScale
        );
    }

    /**
     * Builds a simple frequency distribution with one variable
     * @param CrosstabTree $tree
     * @return Crosstab
     */
    private function buildSimple(CrosstabTree $tree): Crosstab
    {
        list ($firstVariable) = $tree->getFirstAndLastVariablesInTree();

        $lastCategoryKey = array_key_last($firstVariable->categories);
        $lastCategory = '';

        if (is_int($lastCategoryKey)) {
            $lastCategory = $firstVariable->categories[$lastCategoryKey]->name;
        }

        $dataHeaders = $this->getDataHeaders();
        $dataHeaderKeys = array_keys($dataHeaders);
        /** @var positive-int $dataHeaderCount */
        $dataHeaderCount = count($dataHeaders);
        $tableWidth = $dataHeaderCount + 1;

        $rows = [];

        if (null !== $this->title) {
            $rows[] = [CrosstabCell::header($this->title, $tableWidth, attributes: [
                'class' => CrosstabCell::APPEARANCE_TITLE
            ])];
        }

        $axisRow = [
            CrosstabCell::header($firstVariable->description, attributes: ['class' => CrosstabCell::APPEARANCE_Y_AXIS])
        ];

        foreach ($dataHeaders as $dataHeader) {
            $axisRow[] = CrosstabCell::header($dataHeader, attributes: [
                'class' => CrosstabCell::APPEARANCE_X_AXIS_CATEGORY_LABEL
            ]);
        }

        $rows[] = $axisRow;

        $isLastCategory = false;
        $rowIndex = count($rows) - 1;

        foreach ($tree as $node) {
            $payload = $node->payload;

            if ($payload instanceof CrosstabTreeCategoryPayload) {
                $attr = ['class' => CrosstabCell::APPEARANCE_Y_AXIS_CATEGORY_LABEL_SIMPLE];

                if ($payload->isTotal) {
                    $attr['class'] .= ' ' . CrosstabCell::APPEARANCE_TOTAL_LABEL;
                }

                $isLastCategory = $payload->category->name === $lastCategory;

                if ($isLastCategory) {
                    $attr['class'] .= ' ' . CrosstabCell::APPEARANCE_BOTTOM_CELL;
                }

                $rowIndex++;
                $rows[] = [CrosstabCell::header($payload->category->description, attributes: $attr)];
                continue;
            }

            if (!($payload instanceof CrosstabDataItem)) {
                continue;
            }

            $nodeArr = $payload->toArray();

            foreach ($dataHeaderKeys as $dataType) {
                $percent = self::isDataTypePercent($dataType);

                $value = CrosstabExtractionUtilities::extractNumeric($dataType, $nodeArr);

                $attr = ['class' => CrosstabCell::APPEARANCE_CELL];

                if ($isLastCategory) {
                    $attr['class'] .= ' ' . CrosstabCell::APPEARANCE_BOTTOM_CELL;
                }

                if ($payload->isTotal) {
                    $attr['class'] .= ' ' . CrosstabCell::APPEARANCE_TOTAL;
                }

                $dataCell = CrosstabCell::dataCell($this->formatNumber($value, $percent), $value, attributes: $attr);

                $rows[$rowIndex][] = $dataCell;
            }
        }

        return new Crosstab($rows, $this->treeBuilder->getMatrix($tree));
    }

    /**
     * @param string $dataType
     * @return bool
     */
    private static function isDataTypePercent(string $dataType): bool
    {
        return in_array($dataType, [
            CrosstabDataItem::EXPECTED_PERCENT,
            CrosstabDataItem::PERCENT,
            CrosstabDataItem::WEIGHTED_EXPECTED_PERCENT,
            CrosstabDataItem::WEIGHTED_PERCENT
        ]);
    }

    /**
     * Formats a number in various ways
     * @param mixed $valueParsed
     * @param bool $percent
     * @return string
     */
    private function formatNumber(mixed $valueParsed, bool $percent): string
    {
        $valueParsed = CrosstabCastingUtilities::toNumeric($valueParsed);

        if (null === $valueParsed) {
            return $this->messageNil; // NULL values (percentages of zero, etc.) get a special label
        }

        $valueParsed = (float)$valueParsed;

        $type = $percent ? CrosstabNumberFormatterType::Percent : CrosstabNumberFormatterType::Decimal;

        if ($percent) {
            $scale = $this->scalePercent;
        } elseif (CrosstabMathUtilities::isWholeNumber($valueParsed)) {
            $scale = 0;
        } else {
            $scale = $this->scaleDecimal;
        }

        return (string)$this->formatterFlyweight
            ->getNumberFormatter($type, $this->locale, $scale)
            ->format($valueParsed);
    }

    /**
     * Builds a crosstab or layered crosstab
     * @param CrosstabTree $tree
     * @return Crosstab
     */
    private function buildComplex(CrosstabTree $tree): Crosstab
    {
        list($firstVariable, $lastVariable) = $tree->getFirstAndLastVariablesInTree();

        // a whole lot of ugly...
        $colCategoryCount = count($lastVariable->categories) + 1;
        $colCounter = 0; // used to keep track of the row to which we're writing data cells
        $currentVariableDepth = 0;
        $currentVariable = new CrosstabVariable(' ');
        $dataHeaders = $this->getDataHeaders();
        $dataHeaderKeys = array_keys($dataHeaders);
        /** @var positive-int $dataHeaderCount */
        $dataHeaderCount = count($dataHeaders);
        /** @var array<int, int> $depthToRowIndexMap */
        $depthToRowIndexMap = [];
        $lastDataType = array_key_last($dataHeaders);
        $variableByDepth = [];
        $variableDepthByDepth = [];
        $variableFlushed = false;

        $rows = $this->buildCrosstabHeader(
            $firstVariable,
            $lastVariable,
            count($lastVariable->categories) + 1,
            max(count($tree) - 1, 1) * 2
        );

        $topRowIndex = count($rows);

        /**
         * Closure for writing a cell to the table. Keeps track of the current row index for each column
         * @param CrosstabCell $cell
         * @param int $depth
         * @return void
         */
        $write = function (CrosstabCell $cell, int $depth) use (&$depthToRowIndexMap, &$rows, $topRowIndex): void {
            /** @var array<int, int> $depthToRowIndexMap */
            $rowIndex = $depthToRowIndexMap[$depth] ?? null;

            if (null === $rowIndex) {
                $rowIndex = $topRowIndex;
                $depthToRowIndexMap[$depth] = $rowIndex;
            }

            /** @var array<int, array<int, CrosstabCell>> $rows */
            if (!isset($rows[$rowIndex])) {
                $rows[$rowIndex] = [];
            }

            $rows[$rowIndex][] = $cell;

            $depthToRowIndexMap[$depth] += $cell->rowspan;
        };

        foreach ($tree as $node) {
            $depth = $tree->getDepth();

            $payload = $node->payload;

            if (!isset($variableByDepth[$depth])) {
                $variableByDepth[$depth] = $currentVariable;
            } else {
                $currentVariable = $variableByDepth[$depth];
            }

            if (!isset($variableDepthByDepth[$depth])) {
                $variableDepthByDepth[$depth] = $currentVariableDepth;
            } else {
                $currentVariableDepth = $variableDepthByDepth[$depth];
            }

            if ($payload instanceof CrosstabVariable) {
                // variable node detected. Save this to memory so as we don't lose our place as we move up and down the
                // tree
                $currentVariable = $payload;
                $currentVariableDepth = $depth;
                $variableDepthByDepth[$depth] = $currentVariableDepth;
                $variableByDepth[$depth] = $currentVariable;
                $variableFlushed = false;
                continue;
            }

            if ($payload instanceof CrosstabTreeCategoryPayload) {
                $variableName = $currentVariable->name;

                $isFirst = $variableName === $firstVariable->name;
                $isLast = $variableName === $lastVariable->name;

                if (!$variableFlushed && $isLast) {
                    // write the data cells ("Frequencies," etc.) to the Y axis
                    foreach ($dataHeaders as $dataType => $dataHeader) {
                        $attr = ['class' => CrosstabCell::APPEARANCE_DATA_TYPE];

                        if ($dataType === $lastDataType) {
                            $attr['class'] .= ' ' . CrosstabCell::APPEARANCE_BOTTOM_CELL;
                        }

                        $yAxisLabel = CrosstabCell::header($dataHeader, attributes: $attr);
                        $write($yAxisLabel, $depth);
                    }

                    $variableFlushed = true;
                }

                if ($isLast) {
                    continue;
                }

                if (!$variableFlushed && !$isFirst) {
                    // Ronald Reagan was right about only one thing: trees are a menace! :-(
                    $rowspan = ($node->siblingCount + 1) * max($node->yAxisDescendantCount, 1) * $dataHeaderCount;

                    $yAxisLabel = CrosstabCell::header(
                        $currentVariable->description,
                        rowspan: $rowspan,
                        attributes: ['class' => CrosstabCell::APPEARANCE_Y_AXIS_VARIABLE_LABEL]
                    );

                    $write($yAxisLabel, $currentVariableDepth);

                    $variableFlushed = true;
                }

                // write the current row variable category to the Y axis
                $attr = ['class' => CrosstabCell::APPEARANCE_Y_AXIS_CATEGORY_LABEL];

                if ($payload->isTotal) {
                    $attr['class'] .= ' ' . CrosstabCell::APPEARANCE_TOTAL_LABEL;
                }

                $yAxisCategoryLabel = CrosstabCell::header(
                    $payload->category->description,
                    rowspan: max($node->yAxisDescendantCount, 1) * $dataHeaderCount,
                    attributes: $attr,
                );
                $write($yAxisCategoryLabel, $depth);
                continue;
            }

            if (!($payload instanceof CrosstabDataItem)) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }

            $nodeArr = $payload->toArray();

            // write the data cells
            foreach ($dataHeaderKeys as $dataType) {
                $percent = self::isDataTypePercent($dataType);

                $value = CrosstabExtractionUtilities::extractNumeric($dataType, $nodeArr);

                $attr = ['class' => CrosstabCell::APPEARANCE_CELL];

                if ($payload->isTotal) {
                    $attr['class'] .= ' ' . CrosstabCell::APPEARANCE_TOTAL;
                }

                if ($dataType === $lastDataType) {
                    $attr['class'] .= ' ' . CrosstabCell::APPEARANCE_BOTTOM_CELL;
                }

                $dataCell = CrosstabCell::dataCell($this->formatNumber($value, $percent), $value, attributes: $attr);

                $write($dataCell, $depth + ($colCounter % $colCategoryCount));
            }

            $colCounter++;
        }

        /** @var array<int, array<int, CrosstabCell>> $rows */
        return new Crosstab($rows, $this->treeBuilder->getMatrix($tree));
    }

    /**
     * @param CrosstabVariable $rowVariable
     * @param CrosstabVariable $colVariable
     * @param positive-int $xAxisWidth
     * @param positive-int $yAxisWidth
     * @return array<int, array<int, CrosstabCell>>
     */
    private function buildCrosstabHeader(
        CrosstabVariable $rowVariable,
        CrosstabVariable $colVariable,
        int $xAxisWidth,
        int $yAxisWidth
    ): array {
        $tableWidth = $xAxisWidth + $yAxisWidth;

        /** @var array<int, array<int, CrosstabCell>> $rows */
        $rows = [];

        if (null !== $this->title) {
            $rows[] = [CrosstabCell::header($this->title, $tableWidth, attributes: [
                'class' => CrosstabCell::APPEARANCE_TITLE])
            ];
        }

        $rows[] = [
            CrosstabCell::header($rowVariable->description, $yAxisWidth, 2, attributes: [
                'class' => CrosstabCell::APPEARANCE_Y_AXIS
            ]),
            CrosstabCell::header($colVariable->description, $xAxisWidth, attributes: [
                'class' => CrosstabCell::APPEARANCE_X_AXIS
            ])
        ];

        $xAxisRow = [];

        foreach ($colVariable->categories as $category) {
            $xAxisRow[] = CrosstabCell::header($category->description, attributes: [
                'class' => CrosstabCell::APPEARANCE_X_AXIS_CATEGORY_LABEL
            ]);
        }

        $xAxisRow[] = CrosstabCell::header($this->messageTotal, attributes: [
            'class' => CrosstabCell::APPEARANCE_X_AXIS_CATEGORY_LABEL
                . ' '
                . CrosstabCell::APPEARANCE_TOTAL_LABEL
        ]);

        return [...$rows, $xAxisRow];
    }
}
