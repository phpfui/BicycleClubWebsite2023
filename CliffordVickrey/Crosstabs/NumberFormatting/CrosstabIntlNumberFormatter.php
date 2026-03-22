<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\NumberFormatting;

use NumberFormatter;

/**
 * @internal
 */
final readonly class CrosstabIntlNumberFormatter implements CrosstabNumberFormatterInterface
{
    /**
     * @param NumberFormatter $subject
     */
    public function __construct(private NumberFormatter $subject)
    {
    }

    /**
     * @inheritDoc
     */
    public function format(float|int $num, ?int $type = null): string|false
    {
        if (null === $type) {
            return $this->subject->format($num);
        }

        return $this->subject->format($num, $type);
    }

    /**
     * @inheritDoc
     */
    public function getAttribute(int $attribute): int|float|false
    {
        return $this->subject->getAttribute($attribute);
    }

    /**
     * @inheritDoc
     */
    public function getLocale(): string|false
    {
        return $this->subject->getLocale();
    }

    /**
     * @inheritDoc
     */
    public function setAttribute(int $attribute, int $value): bool
    {
        return $this->subject->setAttribute($attribute, $value);
    }
}
