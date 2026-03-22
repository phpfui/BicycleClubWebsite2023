<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\Utilities;

use CliffordVickrey\Crosstabs\Utilities\Strategy\CrosstabBcMathStrategy;
use CliffordVickrey\Crosstabs\Utilities\Strategy\CrosstabFloatingPointMathStrategy;

use function extension_loaded;

/**
 * @internal
 */
final readonly class CrosstabMath implements CrosstabMathInterface
{
    private CrosstabMathInterface $delegate;

    /**
     * @param bool|null $useBcMath
     */
    public function __construct(?bool $useBcMath = null)
    {
        if (null === $useBcMath) {
            $useBcMath = extension_loaded('bcmath');
        }

        $this->delegate = $useBcMath ? new CrosstabBcMathStrategy() : new CrosstabFloatingPointMathStrategy();
    }

    /**
     * @inheritDoc
     */
    public function add(mixed $addendA, mixed $addendB, int $scale = self::DEFAULT_SCALE): float
    {
        return $this->delegate->add($addendA, $addendB, $scale);
    }

    /**
     * @inheritDoc
     */
    public function divide(mixed $dividend, mixed $divisor, int $scale = self::DEFAULT_SCALE): ?float
    {
        return $this->delegate->divide($dividend, $divisor, $scale);
    }

    /**
     * @inheritDoc
     */
    public function multiply(mixed $factorA, mixed $factorB, int $scale = self::DEFAULT_SCALE): float
    {
        return $this->delegate->multiply($factorA, $factorB, $scale);
    }

    /**
     * @inheritDoc
     */
    public function subtract(mixed $minuend, mixed $subtrahend, int $scale = self::DEFAULT_SCALE): float
    {
        return $this->delegate->subtract($minuend, $subtrahend, $scale);
    }

    /**
     * @inheritDoc
     */
    public function pow(mixed $num, mixed $exponent, int $scale = self::DEFAULT_SCALE): float
    {
        return $this->delegate->pow($num, $exponent, $scale);
    }
}
