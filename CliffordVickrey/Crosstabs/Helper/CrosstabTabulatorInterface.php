<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\Helper;

use CliffordVickrey\Crosstabs\Options\CrosstabVariableCollection;
use CliffordVickrey\Crosstabs\SourceData\CrosstabSourceDataCollection;
use CliffordVickrey\Crosstabs\Utilities\CrosstabMathInterface;

/**
 * @internal
 */
interface CrosstabTabulatorInterface
{
    /**
     * Tabulates the frequency and weighted frequency of every possible combination of variable categories
     * @param CrosstabVariableCollection $variables
     * @param CrosstabSourceDataCollection $sourceData
     * @param positive-int $scale
     * @return array{n: array<string, float>, weightedN: array<string, float>}
     */
    public function tabulate(
        CrosstabVariableCollection $variables,
        CrosstabSourceDataCollection $sourceData,
        int $scale = CrosstabMathInterface::DEFAULT_SCALE
    ): array;
}
