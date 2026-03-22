<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\NumberFormatting;

use function extension_loaded;
use function sprintf;

/**
 * @internal A simple utility to retrieve and store number formatters
 */
class CrosstabNumberFormatterFlyweight implements CrosstabNumberFormatterFlyweightInterface
{
    /** @var array<string, CrosstabNumberFormatterInterface> */
    private array $formatters = [];

    /**
     * @inheritDoc
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    public function getNumberFormatter(
        CrosstabNumberFormatterType $type,
        string $locale,
        int $scale = 0
    ): CrosstabNumberFormatterInterface {
        $key = sprintf('%s|%s|%d', $type->name, $locale, $scale);

        if (isset($this->formatters[$key])) {
            return $this->formatters[$key];
        }

        if (extension_loaded('intl')) {
            $intlNf = new \NumberFormatter($locale, $type->toIntlConstant());
            $nf = new CrosstabIntlNumberFormatter($intlNf);
            $maxFractionDigits = \NumberFormatter::MAX_FRACTION_DIGITS;
        } else {
            // @codeCoverageIgnoreStart
            $nf = new CrosstabPolyfillNumberFormatter($type === CrosstabNumberFormatterType::Percent);
            $maxFractionDigits = 6;
            // @codeCoverageIgnoreEnd
        }

        $nf->setAttribute($maxFractionDigits, $scale);
        $this->formatters[$key] = $nf;
        return $nf;
    }
}
