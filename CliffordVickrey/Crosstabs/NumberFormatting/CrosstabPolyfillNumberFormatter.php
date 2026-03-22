<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\NumberFormatting;

use function number_format;

/**
 * @internal
 */
class CrosstabPolyfillNumberFormatter implements CrosstabNumberFormatterInterface
{
    private int $scale = 0;

    /**
     * @param bool $isPercent
     */
    public function __construct(private readonly bool $isPercent = false)
    {
    }

    /**
     * @inheritDoc
     */
    public function format(float|int $num, ?int $type = null): string|false
    {
        if ($this->isPercent) {
            $leading = '%';
            $num *= 100;
        } else {
            $leading = '';
        }

        return $leading . number_format((float)$num, $this->scale);
    }

    /**
     * @inheritDoc
     */
    public function getAttribute(int $attribute): int|float|false
    {
        if (6 === $attribute) {
            return $this->scale;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function getLocale(): string|false
    {
        return 'en_US';
    }

    /**
     * @inheritDoc
     */
    public function setAttribute(int $attribute, int $value): bool
    {
        if (6 === $attribute) {
            $this->scale = $value;
            return true;
        }

        return false;
    }
}
