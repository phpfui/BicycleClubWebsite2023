<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\Helper;

use CliffordVickrey\Crosstabs\Options\CrosstabCategory;
use CliffordVickrey\Crosstabs\Options\CrosstabVariable;
use CliffordVickrey\Crosstabs\Options\CrosstabVariableCollection;
use CliffordVickrey\Crosstabs\SourceData\CrosstabSourceDataCollection;
use CliffordVickrey\Crosstabs\Utilities\CrosstabCastingUtilities;

use function array_intersect_key;
use function array_merge;
use function array_reduce;
use function array_reverse;
use function array_values;
use function count;
use function strnatcasecmp;
use function usort;

/**
 * @internal
 */
final readonly class CrosstabVariableCollector implements CrosstabVariableCollectorInterface
{
    /**
     * @inheritDoc
     */
    public function collectVariables(
        CrosstabSourceDataCollection $sourceData,
        CrosstabVariable|array $rowVariable = [],
        CrosstabVariable|array $colVariable = [],
        iterable $layers = [],
    ): CrosstabVariableCollection {
        $parsedVariables = [];

        $variables = [self::buildVariable($rowVariable), self::buildVariable($colVariable)];

        foreach ($layers as $layer) {
            $variables[] = self::buildVariable($layer);
        }

        foreach ($variables as $variable) {
            if (null === $variable) {
                continue;
            }

            $sort = empty($variable->categories);

            $categoriesIndexedByName = array_reduce(
                $variable->categories,
                static fn(array $carry, CrosstabCategory $category) => array_merge(
                    $carry,
                    [$category->name => $category]
                ),
                []
            );

            $nonEmptyCategoriesIndexedByName = [];

            foreach ($sourceData as $row) {
                $value = CrosstabCastingUtilities::toString($row->getValue($variable->name));

                if ('' === $value) {
                    continue;
                }

                $nonEmptyCategoriesIndexedByName[$value] = true;

                if (isset($categoriesIndexedByName[$value])) {
                    continue;
                }

                $categoriesIndexedByName[$value] = new CrosstabCategory($value);
            }

            /** @var list<CrosstabCategory> $categories */
            $categories = array_values(array_intersect_key($categoriesIndexedByName, $nonEmptyCategoriesIndexedByName));

            if ($sort) {
                usort($categories, static function (CrosstabCategory $a, CrosstabCategory $b) {
                    $compA = strnatcasecmp($a->description, $b->description);

                    if (0 !== $compA) {
                        return $compA;
                    }

                    // @codeCoverageIgnoreStart
                    return strnatcasecmp($a->name, $b->name);
                    // @codeCoverageIgnoreEnd
                });
            }

            $parsedVariables[] = new CrosstabVariable($variable->name, $variable->description, $categories);
        }

        return CrosstabVariableCollection::__set_state(['variables' => self::inLeftToRightOrder($parsedVariables)]);
    }

    /**
     * @param array<string, mixed>|CrosstabVariable $variable
     * @return CrosstabVariable|null
     */
    private static function buildVariable(array|CrosstabVariable $variable): ?CrosstabVariable
    {
        if ($variable instanceof CrosstabVariable) {
            return $variable;
        }

        if (0 === count($variable)) {
            return null;
        }

        return CrosstabVariable::__set_state($variable);
    }

    /**
     * Orders variables by where they appear in the grid, from left to right
     * @param list<CrosstabVariable> $variables
     * @return list<CrosstabVariable>
     */
    private static function inLeftToRightOrder(array $variables): array
    {
        if (count($variables) < 2) {
            return $variables;
        }

        $xKey = 1;

        $x = $variables[$xKey];

        $y = [];

        foreach ($variables as $k => $variable) {
            if ($k === $xKey) {
                continue;
            }

            $y[] = $variable;
        }

        return [...array_reverse($y), $x]; // layers, then row variable, then column variable
    }
}
