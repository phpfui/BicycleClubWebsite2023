<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\Helper;

use CliffordVickrey\Crosstabs\Options\CrosstabVariable;
use CliffordVickrey\Crosstabs\Options\CrosstabVariableCollection;
use CliffordVickrey\Crosstabs\SourceData\CrosstabSourceDataCollection;

/**
 * @internal
 */
interface CrosstabVariableCollectorInterface
{
    /**
     * Gets a collection of layer, row, and column variables, each with a complete list of categories that belong to the
     * dataset
     * @param CrosstabSourceDataCollection $sourceData
     * @param CrosstabVariable|array<string, mixed> $rowVariable
     * @param CrosstabVariable|array<string, mixed> $colVariable
     * @param iterable<mixed, CrosstabVariable|array<string, mixed>> $layers
     * @return CrosstabVariableCollection
     */
    public function collectVariables(
        CrosstabSourceDataCollection $sourceData,
        CrosstabVariable|array $rowVariable = [],
        CrosstabVariable|array $colVariable = [],
        iterable $layers = [],
    ): CrosstabVariableCollection;
}
