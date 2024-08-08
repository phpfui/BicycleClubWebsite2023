<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Utils;

use Nette;
use TypeError;


/**
 * Provides safe, lossless type casting. Throws TypeError if conversion would result
 * in data loss. Supports bool, int, float, string, and array types.
 */
final class Cast
{
	use Nette\StaticClass;

	/**
	 * Safely converts a value to a specified type. Supported types: bool, int, float, string, array.
	 * @throws TypeError if the value cannot be converted
	 */
	public static function to(mixed $value, string $type): mixed
	{
		return match ($type) {
			'bool' => self::toBool($value),
			'int' => self::toInt($value),
			'float' => self::toFloat($value),
			'string' => self::toString($value),
			'array' => self::toArray($value),
			default => throw new TypeError("Unsupported type '$type'."),
		};
	}


	/**
	 * Safely converts a value to a specified type or returns null if the value is null.
	 * Supported types: bool, int, float, string, array.
	 * @throws TypeError if the value cannot be converted
	 */
	public static function toOrNull(mixed $value, string $type): mixed
	{
		return $value === null ? null : self::to($value, $type);
	}


	/**
	 * Safely converts a value to a boolean.
	 * @throws TypeError if the value cannot be converted
	 */
	public static function toBool(mixed $value): bool
	{
		return match (true) {
			is_bool($value) => $value,
			is_int($value) => $value !== 0,
			is_float($value) => $value !== 0.0,
			is_string($value) => $value !== '' && $value !== '0',
			$value === null => false,
			default => throw new TypeError('Cannot cast ' . get_debug_type($value) . ' to bool.'),
		};
	}


	/**
	 * Safely converts a value to an integer.
	 * @throws TypeError if the value cannot be converted
	 */
	public static function toInt(mixed $value): int
	{
		return match (true) {
			is_bool($value) => (int) $value,
			is_int($value) => $value,
			is_float($value) => $value === (float) ($tmp = (int) $value)
				? $tmp
				: throw new TypeError('Cannot cast ' . self::toString($value) . ' to int.'),
			is_string($value) => preg_match('~^-?\d+(\.0*)?$~D', $value)
				? (int) $value
				: throw new TypeError("Cannot cast '$value' to int."),
			$value === null => 0,
			default => throw new TypeError('Cannot cast ' . get_debug_type($value) . ' to int.'),
		};
	}


	/**
	 * Safely converts a value to a float.
	 * @throws TypeError if the value cannot be converted
	 */
	public static function toFloat(mixed $value): float
	{
		return match (true) {
			is_bool($value) => $value ? 1.0 : 0.0,
			is_int($value) => (float) $value,
			is_float($value) => $value,
			is_string($value) => preg_match('~^-?\d+(\.\d*)?$~D', $value)
				? (float) $value
				: throw new TypeError("Cannot cast '$value' to float."),
			$value === null => 0.0,
			default => throw new TypeError('Cannot cast ' . get_debug_type($value) . ' to float.'),
		};
	}


	/**
	 * Safely converts a value to a string.
	 * @throws TypeError if the value cannot be converted
	 */
	public static function toString(mixed $value): string
	{
		return match (true) {
			is_bool($value) => $value ? '1' : '0',
			is_int($value) => (string) $value,
			is_float($value) => str_contains($tmp = (string) $value, '.') ? $tmp : $tmp . '.0',
			is_string($value) => $value,
			$value === null => '',
			default => throw new TypeError('Cannot cast ' . get_debug_type($value) . ' to string.'),
		};
	}


	/**
	 * Wraps the value in an array if it is not already one or returns empty array if the value is null.
	 */
	public static function toArray(mixed $value): array
	{
		return match (true) {
			is_array($value) => $value,
			$value === null => [],
			default => [$value],
		};
	}


	/**
	 * Safely converts a value to a boolean or returns null if the value is null.
	 * @throws TypeError if the value cannot be converted
	 */
	public static function toBoolOrNull(mixed $value): ?bool
	{
		return $value === null ? null : self::toBool($value);
	}


	/**
	 * Safely converts a value to an integer or returns null if the value is null.
	 * @throws TypeError if the value cannot be converted
	 */
	public static function toIntOrNull(mixed $value): ?int
	{
		return $value === null ? null : self::toInt($value);
	}


	/**
	 * Safely converts a value to a float or returns null if the value is null.
	 * @throws TypeError if the value cannot be converted
	 */
	public static function toFloatOrNull(mixed $value): ?float
	{
		return $value === null ? null : self::toFloat($value);
	}


	/**
	 * Safely converts a value to a string or returns null if the value is null.
	 * @throws TypeError if the value cannot be converted
	 */
	public static function toStringOrNull(mixed $value): ?string
	{
		return $value === null ? null : self::toString($value);
	}


	/**
	 * Wraps the value in an array if it is not already one or returns null if the value is null.
	 */
	public static function toArrayOrNull(mixed $value): ?array
	{
		return $value === null ? null : self::toArray($value);
	}


	/**
	 * Converts false to null, does not change other values.
	 */
	public static function falseToNull(mixed $value): mixed
	{
		return $value === false ? null : $value;
	}
}
