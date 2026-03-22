<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\Exception;

use InvalidArgumentException;

class CrosstabInvalidArgumentException extends InvalidArgumentException implements CrosstabThrowable
{
}
