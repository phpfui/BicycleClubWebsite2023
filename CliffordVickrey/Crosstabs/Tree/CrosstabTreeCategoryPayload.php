<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\Tree;

use CliffordVickrey\Crosstabs\Options\CrosstabCategory;

/**
 * @internal
 */
final readonly class CrosstabTreeCategoryPayload
{
    /**
     * @param CrosstabCategory $category
     * @param bool $isTotal
     */
    public function __construct(public CrosstabCategory $category, public bool $isTotal = false)
    {
    }
}
