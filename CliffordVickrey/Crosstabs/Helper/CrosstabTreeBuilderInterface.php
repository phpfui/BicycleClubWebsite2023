<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\Helper;

use CliffordVickrey\Crosstabs\Crosstab\CrosstabDataItem;
use CliffordVickrey\Crosstabs\Options\CrosstabPercentType;
use CliffordVickrey\Crosstabs\Options\CrosstabVariableCollection;
use CliffordVickrey\Crosstabs\Tree\CrosstabTree;
use CliffordVickrey\Crosstabs\Utilities\CrosstabMathInterface;

/**
 * @internal
 */
interface CrosstabTreeBuilderInterface
{
    /**
     * Recursively iterates through the variable tree and populates it with data
     * @param CrosstabVariableCollection $variables
     * @param array{n: array<string, float>, weightedN: array<string, float>} $totals
     * @param CrosstabPercentType $percentType
     * @param non-empty-string $messageTotal
     * @param positive-int $scale
     * @return CrosstabTree
     */
    public function buildTree(
        CrosstabVariableCollection $variables,
        array $totals,
        CrosstabPercentType $percentType = CrosstabPercentType::Total,
        string $messageTotal = 'Total',
        int $scale = CrosstabMathInterface::DEFAULT_SCALE
    ): CrosstabTree;

    /**
     * @param CrosstabTree $tree
     * @return list<list<CrosstabDataItem>>
     */
    public function getMatrix(CrosstabTree $tree): array;
}
