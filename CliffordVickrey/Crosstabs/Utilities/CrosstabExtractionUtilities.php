<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\Utilities;

use CliffordVickrey\Crosstabs\Exception\CrosstabUnexpectedValueException;

/**
 * @internal
 */
class CrosstabExtractionUtilities
{
    /**
     * @param string $key
     * @param array<array-key, mixed> $arr
     * @return float|null
     */
    public static function extractFloat(string $key, array $arr): float|null
    {
        $numeric = self::extractNumeric($key, $arr);

        if (null === $numeric) {
            return null;
        }

        return (float)$numeric;
    }

    /**
     * @param string $key
     * @param array<array-key, mixed> $arr
     * @return float|int|null
     */
    public static function extractNumeric(string $key, array $arr): float|int|null
    {
        return CrosstabCastingUtilities::toNumeric($arr[$key] ?? null);
    }

    /**
     * @param string $key
     * @param array<array-key, mixed> $arr
     * @return int<0, max>|null
     */
    public static function extractAbsoluteInt(string $key, array $arr): int|null
    {
        return CrosstabCastingUtilities::toAbsoluteInt($arr[$key] ?? null);
    }

    /**
     * @param string $key
     * @param array<array-key, mixed> $arr
     * @return positive-int|null
     */
    public static function extractPositiveInt(string $key, array $arr): int|null
    {
        return CrosstabCastingUtilities::toPositiveInt($arr[$key] ?? null);
    }

    /**
     * @param string $key
     * @param array<array-key, mixed> $arr
     * @return string
     */
    public static function extractString(string $key, array $arr): string
    {
        return CrosstabCastingUtilities::toString($arr[$key] ?? null);
    }

    /**
     * @param string $key
     * @param array<array-key, mixed> $arr
     * @return string|null
     */
    public static function extractNonEmptyString(string $key, array $arr): string|null
    {
        return CrosstabCastingUtilities::toNonEmptyString($arr[$key] ?? null);
    }

    /**
     * @param string $key
     * @param array<array-key, mixed> $arr
     * @return non-empty-string
     */
    public static function extractNonEmptyStringRequired(string $key, array $arr): string
    {
        $str = CrosstabCastingUtilities::toNonEmptyString($arr[$key] ?? null);

        if (null === $str) {
            throw CrosstabUnexpectedValueException::fromValue(
                "array value \"$key\"",
                'non-empty-string'
            );
        }

        return $str;
    }

    /**
     * @param string $key
     * @param array<array-key, mixed> $arr
     * @return int
     */
    public static function extractPositiveIntRequired(string $key, array $arr): int
    {
        $intVal = CrosstabCastingUtilities::toPositiveInt($arr[$key] ?? null);

        if (null === $intVal) {
            throw CrosstabUnexpectedValueException::fromValue(
                "array value \"$key\"",
                'positive-int'
            );
        }

        return $intVal;
    }
}
