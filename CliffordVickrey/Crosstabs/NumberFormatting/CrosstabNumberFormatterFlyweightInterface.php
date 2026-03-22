<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\NumberFormatting;

/**
 * @internal
 */
interface CrosstabNumberFormatterFlyweightInterface
{
    /**
     * @param CrosstabNumberFormatterType $type
     * @param non-empty-string $locale
     * @param int<0, max> $scale
     * @return CrosstabNumberFormatterInterface
     */
    public function getNumberFormatter(
        CrosstabNumberFormatterType $type,
        string $locale,
        int $scale = 0
    ): CrosstabNumberFormatterInterface;
}
