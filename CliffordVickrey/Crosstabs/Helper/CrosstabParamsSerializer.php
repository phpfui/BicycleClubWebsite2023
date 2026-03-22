<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\Helper;

use function http_build_query;

/**
 * @internal
 */
class CrosstabParamsSerializer implements CrosstabParamsSerializerInterface
{
    /**
     * @inheritDoc
     */
    public function serializeParams(array $params): string
    {
        return http_build_query($params); // good enough!
    }
}
