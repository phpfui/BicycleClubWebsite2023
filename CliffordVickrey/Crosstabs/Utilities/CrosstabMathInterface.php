<?php

namespace CliffordVickrey\Crosstabs\Utilities;

/**
 * @internal
 */
interface CrosstabMathInterface
{
    public const DEFAULT_SCALE = 16;

    /**
     * @param mixed $addendA
     * @param mixed $addendB
     * @param int $scale
     * @return float
     */
    public function add(mixed $addendA, mixed $addendB, int $scale = self::DEFAULT_SCALE): float;

    /**
     * @param mixed $dividend
     * @param mixed $divisor
     * @param int $scale
     * @return float|null
     */
    public function divide(mixed $dividend, mixed $divisor, int $scale = self::DEFAULT_SCALE): ?float;

    /**
     * @param mixed $factorA
     * @param mixed $factorB
     * @param int $scale
     * @return float
     */
    public function multiply(mixed $factorA, mixed $factorB, int $scale = self::DEFAULT_SCALE): float;

    /**
     * @param mixed $minuend
     * @param mixed $subtrahend
     * @param int $scale
     * @return float
     */
    public function subtract(mixed $minuend, mixed $subtrahend, int $scale = self::DEFAULT_SCALE): float;

    /**
     * @param mixed $num
     * @param mixed $exponent
     * @param int $scale
     * @return float
     */
    public function pow(mixed $num, mixed $exponent, int $scale = self::DEFAULT_SCALE): float;
}
