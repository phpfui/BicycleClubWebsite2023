<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs;

use CliffordVickrey\Crosstabs\Crosstab\Crosstab;

interface CrosstabBuilderInterface
{
    /**
     * Builds a crosstab based on configured properties
     * @return Crosstab
     */
    public function build(): Crosstab;
}
