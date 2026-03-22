<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\Exception;

use CliffordVickrey\Crosstabs\Utilities\CrosstabCastingUtilities;
use OutOfBoundsException;

use function sprintf;

class CrosstabOutOfBoundsException extends OutOfBoundsException implements CrosstabThrowable
{
    /**
     * @param mixed $offset
     * @return self
     */
    public static function fromIllegalOffset(mixed $offset): self
    {
        $msg = sprintf('Illegal offset, "%s"', CrosstabCastingUtilities::toString($offset));
        return new self($msg);
    }
}
