<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\Utilities\Strategy;

use CliffordVickrey\Crosstabs\Utilities\CrosstabCastingUtilities;
use CliffordVickrey\Crosstabs\Utilities\CrosstabMathInterface;

use function bcadd;
use function bccomp;
use function bcdiv;
use function bcmul;
use function bcpow;
use function bcsub;

/**
 * @internal
 */
class CrosstabBcMathStrategy implements CrosstabMathInterface
{
    /**
     * @inheritDoc
     */
    public function add(mixed $addendA, mixed $addendB, int $scale = self::DEFAULT_SCALE): float
    {
        $addendA = CrosstabCastingUtilities::toNumericString($addendA);
        $addendB = CrosstabCastingUtilities::toNumericString($addendB);
        return (float)bcadd($addendA, $addendB, $scale);
    }

    /**
     * @inheritDoc
     */
    public function divide(mixed $dividend, mixed $divisor, int $scale = self::DEFAULT_SCALE): ?float
    {
        $divisor = CrosstabCastingUtilities::toNumericString($divisor);

        if (0 === bccomp('0', $divisor, $scale)) {
            return null;
        }

        $dividend = (string)(CrosstabCastingUtilities::toNumeric($dividend) ?? 0.0);
        return (float)bcdiv($dividend, $divisor, $scale);
    }

    /**
     * @inheritDoc
     */
    public function multiply(mixed $factorA, mixed $factorB, int $scale = self::DEFAULT_SCALE): float
    {
        $factorA = CrosstabCastingUtilities::toNumericString($factorA);
        $factorB = CrosstabCastingUtilities::toNumericString($factorB);
        return (float)bcmul($factorA, $factorB, $scale);
    }

    /**
     * @inheritDoc
     */
    public function subtract(mixed $minuend, mixed $subtrahend, int $scale = self::DEFAULT_SCALE): float
    {
        $minuend = CrosstabCastingUtilities::toNumericString($minuend);
        $subtrahend = CrosstabCastingUtilities::toNumericString($subtrahend);
        return (float)bcsub($minuend, $subtrahend, $scale);
    }

    /**
     * @inheritDoc
     */
    public function pow(mixed $num, mixed $exponent, int $scale = self::DEFAULT_SCALE): float
    {
        $num = CrosstabCastingUtilities::toNumericString($num);
        $exponent = CrosstabCastingUtilities::toNumericString($exponent);
        return (float)bcpow($num, $exponent, $scale);
    }
}
