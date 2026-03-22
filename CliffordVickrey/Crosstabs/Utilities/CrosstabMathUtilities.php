<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\Utilities;

use function floor;

/**
 * @internal
 */
class CrosstabMathUtilities
{
    /**
     * @param int|float|numeric-string $val
     * @return bool
     */
    public static function isWholeNumber(int|float|string $val): bool
    {
        $val = (float)$val;

        if (floor($val) !== $val) {
            return false;
        }

        return true;
    }

    /**
     * @param list<string> $elements
     * @return list<list<string>>
     */
    public static function getPowerSet(array $elements): array
    {
        $results = [[]];

        foreach ($elements as $element) {
            foreach ($results as $combination) {
                $results[] = [$element, ...$combination];
            }
        }

        return $results;
    }
}
