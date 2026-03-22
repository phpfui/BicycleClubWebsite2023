<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\NumberFormatting;

/**
 * @internal
 */
interface CrosstabNumberFormatterInterface
{
    /**
     * @param int|float $num
     * @param int|null $type
     * @return string|false
     */
    public function format(int|float $num, ?int $type = null): string|false;

    /**
     * @param int $attribute
     * @return int|float|false
     */
    public function getAttribute(int $attribute): int|float|false;

    /**
     * @return string|false
     */
    public function getLocale(): string|false;

    /**
     * @param int $attribute
     * @param int $value
     * @return bool
     */
    public function setAttribute(int $attribute, int $value): bool;
}
