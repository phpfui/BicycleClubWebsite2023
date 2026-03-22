<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\NumberFormatting;

use function extension_loaded;

/**
 * @internal
 */
enum CrosstabNumberFormatterType
{
    case Decimal;
    case Percent;

    /**
     * @return int
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    public function toIntlConstant(): int
    {
        if (!extension_loaded('intl')) {
            // @codeCoverageIgnoreStart
            return self::Decimal === $this ? 1 : 3;
            // @codeCoverageIgnoreEnd
        }

        return self::Decimal === $this ? \NumberFormatter::DECIMAL : \NumberFormatter::PERCENT;
    }
}
