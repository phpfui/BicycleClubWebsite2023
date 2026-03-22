<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\Exception;

use UnexpectedValueException;

use function get_debug_type;
use function sprintf;

class CrosstabUnexpectedValueException extends UnexpectedValueException implements CrosstabThrowable
{
    /**
     * @param mixed $val
     * @param string $expectedType
     * @return self
     */
    public static function fromValue(mixed $val, string $expectedType): self
    {
        $msg = sprintf('Expected %s; got %s', $expectedType, get_debug_type($val));
        return new self($msg);
    }
}
