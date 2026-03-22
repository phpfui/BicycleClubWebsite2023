<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\Exception;

use LogicException;

class CrosstabLogicException extends LogicException implements CrosstabThrowable
{
}
