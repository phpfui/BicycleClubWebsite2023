<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\Helper;

use CliffordVickrey\Crosstabs\Crosstab\CrosstabDataItem;
use CliffordVickrey\Crosstabs\Exception\CrosstabInvalidArgumentException;
use CliffordVickrey\Crosstabs\Options\CrosstabPercentType;
use CliffordVickrey\Crosstabs\Options\CrosstabVariable;
use CliffordVickrey\Crosstabs\Options\CrosstabVariableCollection;
use CliffordVickrey\Crosstabs\Tree\CrosstabTree;
use CliffordVickrey\Crosstabs\Tree\CrosstabTreeCategoryPayload;
use CliffordVickrey\Crosstabs\Utilities\CrosstabMath;
use CliffordVickrey\Crosstabs\Utilities\CrosstabMathInterface;
use WeakMap;

use function array_diff_key;
use function array_filter;
use function array_intersect_key;
use function array_pop;

/**
 * @internal
 */
final class CrosstabTreeBuilder implements CrosstabTreeBuilderInterface
{
    private CrosstabMath $math;
    private CrosstabParamsSerializerInterface $serializer;
    /** @var WeakMap<CrosstabTree, list<list<CrosstabDataItem>>> */
    private WeakMap $treeToMatrixMap;

    /**
     * @param CrosstabParamsSerializerInterface|null $serializer
     */
    public function __construct(?CrosstabParamsSerializerInterface $serializer = null)
    {
        $this->math = new CrosstabMath();
        $this->serializer = $serializer ?? new CrosstabParamsSerializer();
        /** @psalm-suppress PropertyTypeCoercion */
        $this->treeToMatrixMap = new WeakMap();
    }

    /**
     * @inheritDoc
     * @todo consider using the visitor pattern instead of iteration/type juggling
     */
    public function buildTree(
        CrosstabVariableCollection $variables,
        array $totals,
        CrosstabPercentType $percentType = CrosstabPercentType::Total,
        string $messageTotal = 'Total',
        int $scale = CrosstabMathInterface::DEFAULT_SCALE
    ): CrosstabTree {
        $matrix = [];
        $counter = 0;
        $y = -1;

        $tree = new CrosstabTree($variables, $messageTotal);

        list($rowVar, $colVar) = $this->getRowAndColVariables($variables);

        $cols = count($colVar->categories);

        $query = [];
        $currentVariable = '';

        $totalN = $totals['n'][''] ?? 0.0;
        $totalWeightedN = $totals['weightedN'][''] ?? 0.0;

        $variablesByDepth = [];

        foreach ($tree as $node) {
            $payload = $node->payload;

            $depth = $tree->getDepth();

            if (!isset($variablesByDepth[$depth])) {
                $variablesByDepth[$depth] = $currentVariable;
            } else {
                $currentVariable = $variablesByDepth[$depth];
            }

            if ($payload instanceof CrosstabVariable) {
                $currentVariable = $payload->name;
                $variablesByDepth[$depth] = $payload->name;
                continue;
            }

            if ($payload instanceof CrosstabTreeCategoryPayload) {
                $query[$currentVariable] = $payload->isTotal ? null : $payload->category->name;
                continue;
            }

            if (!($payload instanceof CrosstabDataItem)) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }

            $key = $this->serializer->serializeParams($query);
            $n = $totals['n'][$key] ?? 0.0;

            $weightedN = $totals['weightedN'][$key] ?? 0.0;

            $rowSubTotalKey = $this->serializer->serializeParams(array_diff_key($query, [$colVar->name => true]));
            $rowSubTotalN = $totals['n'][$rowSubTotalKey] ?? 0.0;
            $rowSubTotalWeightedN = $totals['weightedN'][$rowSubTotalKey] ?? 0.0;

            $colSubTotalKey = $this->serializer->serializeParams(array_intersect_key($query, [$colVar->name => true]));
            $colSubTotalN = $totals['n'][$colSubTotalKey] ?? 0.0;
            $colSubTotalWeightedN = $totals['weightedN'][$colSubTotalKey] ?? 0.0;

            $expectedN = $this->math->multiply(
                $colSubTotalN,
                (float)$this->math->divide(
                    $rowSubTotalN,
                    $totalN,
                    $scale
                ),
                $scale
            );

            $expectedWeightedN = $this->math->multiply(
                $colSubTotalWeightedN,
                (float)$this->math->divide(
                    $rowSubTotalWeightedN,
                    $totalWeightedN,
                    $scale
                ),
                $scale
            );

            $of = $this->getPercentDivisors($percentType, $totals, $rowVar, $colVar, $query);

            $payload->expectedFrequency = $expectedN;
            $payload->expectedPercent = $this->math->divide($expectedN, $of['unweighted'], $scale);
            $payload->frequency = $n;
            $payload->isTotal = count(array_filter($query, is_null(...))) > 0;
            $payload->params = $query;
            $payload->percent = $this->math->divide($n, $of['unweighted'], $scale);
            $payload->weightedExpectedFrequency = $expectedWeightedN;
            $payload->weightedExpectedPercent = $this->math->divide($expectedWeightedN, $of['weighted'], $scale);
            $payload->weightedFrequency = $weightedN;
            $payload->weightedPercent = $this->math->divide($weightedN, $of['weighted'], $scale);

            if ($payload->isTotal) {
                continue;
            }

            if (0 === $counter % $cols) {
                $y++;
            }

            if (!isset($matrix[$y])) {
                $matrix[$y] = [];
            }

            $matrix[$y][] = $payload;

            $counter++;
        }

        /** @psalm-suppress ArgumentTypeCoercion It's always a list of lists of objects */
        $this->treeToMatrixMap[$tree] = $matrix;

        return $tree;
    }

    /**
     * @param CrosstabVariableCollection $variables
     * @return array{0: CrosstabVariable, 1: CrosstabVariable}
     */
    private function getRowAndColVariables(CrosstabVariableCollection $variables): array
    {
        $vars = $variables->toArray();

        if (0 === count($vars)) {
            throw new CrosstabInvalidArgumentException('Variable collection cannot be empty');
        }

        $colVar = array_pop($vars);
        $rowVar = $colVar;

        if (0 !== count($vars)) {
            $rowVar = array_pop($vars);
        }

        return [$rowVar, $colVar];
    }

    /**
     * @param CrosstabPercentType $percentType
     * @param array{n: array<string, float>, weightedN: array<string, float>} $totals
     * @param CrosstabVariable $rowVar
     * @param CrosstabVariable $colVar
     * @param array<string, string|null> $query
     * @return array{unweighted: float, weighted: float}
     */
    private function getPercentDivisors(
        CrosstabPercentType $percentType,
        array $totals,
        CrosstabVariable $rowVar,
        CrosstabVariable $colVar,
        array $query
    ): array {
        $key = match ($percentType) {
            CrosstabPercentType::Column => $this->serializer->serializeParams(array_intersect_key($query, [
                $colVar->name => true
            ])),
            CrosstabPercentType::ColumnWithinLayer => $this->serializer->serializeParams(array_diff_key($query, [
                $rowVar->name => true
            ])),
            CrosstabPercentType::Row => $this->serializer->serializeParams(array_diff_key($query, [
                $colVar->name => true
            ])),
            CrosstabPercentType::TotalWithinLayer => $this->serializer->serializeParams(array_diff_key($query, [
                $rowVar->name => true,
                $colVar->name => true
            ])),
            default => ''
        };

        return [
            'unweighted' => $totals['n'][$key] ?? 0.0,
            'weighted' => $totals['weightedN'][$key] ?? 0.0
        ];
    }

    /**
     * @inheritDoc
     */
    public function getMatrix(CrosstabTree $tree): array
    {
        return $this->treeToMatrixMap[$tree] ?? [];
    }
}
