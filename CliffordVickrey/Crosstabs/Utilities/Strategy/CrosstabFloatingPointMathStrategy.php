<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\Utilities\Strategy;

use CliffordVickrey\Crosstabs\Utilities\CrosstabCastingUtilities;
use CliffordVickrey\Crosstabs\Utilities\CrosstabMathInterface;

use function pow;
use function round;

/**
 * @internal
 */
class CrosstabFloatingPointMathStrategy implements CrosstabMathInterface
{
    /**
     * @inheritDoc
     */
    public function add(mixed $addendA, mixed $addendB, int $scale = self::DEFAULT_SCALE): float
    {
        $addendA = (float)(CrosstabCastingUtilities::toNumeric($addendA) ?? 0.0);
        $addendB = (float)(CrosstabCastingUtilities::toNumeric($addendB) ?? 0.0);
        return round($addendA + $addendB, $scale);
    }

    /**
     * @inheritDoc
     */
    public function divide(mixed $dividend, mixed $divisor, int $scale = self::DEFAULT_SCALE): ?float
    {
        $divisor = (float)(CrosstabCastingUtilities::toNumeric($divisor) ?? 0.0);

        if (0.0 === $divisor) {
            return null;
        }

        $dividend = (float)(CrosstabCastingUtilities::toNumeric($dividend) ?? 0.0);

        return round($dividend / $divisor, $scale);
    }

    /**
     * @inheritDoc
     */
    public function multiply(mixed $factorA, mixed $factorB, int $scale = self::DEFAULT_SCALE): float
    {
        $factorA = (float)(CrosstabCastingUtilities::toNumeric($factorA) ?? 0.0);
        $factorB = (float)(CrosstabCastingUtilities::toNumeric($factorB) ?? 0.0);
        return round($factorA * $factorB, $scale);
    }

    /**
     * @inheritDoc
     */
    public function subtract(mixed $minuend, mixed $subtrahend, int $scale = self::DEFAULT_SCALE): float
    {
        $minuend = (float)(CrosstabCastingUtilities::toNumeric($minuend) ?? 0.0);
        $subtrahend = (float)(CrosstabCastingUtilities::toNumeric($subtrahend) ?? 0.0);
        return round($minuend - $subtrahend, $scale);
    }

    /**
     * @inheritDoc
     */
    public function pow(mixed $num, mixed $exponent, int $scale = self::DEFAULT_SCALE): float
    {
        $num = (float)(CrosstabCastingUtilities::toNumeric($num) ?? 0.0);
        $exponent = (float)(CrosstabCastingUtilities::toNumeric($exponent) ?? 0.0);
        return round(pow($num, $exponent), $scale);
    }
}
