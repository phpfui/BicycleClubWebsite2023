<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\Options;

enum CrosstabPercentType: string
{
    case Column = 'column'; // column percentages
    case ColumnWithinLayer = 'columnWithinLayer'; // column percentages for each layer (in layered crosstabs)
    case Total = 'total'; // total percentages
    case TotalWithinLayer = 'totalWithinLayer'; // total percentages for each layer (in layered crosstabs)
    case Row = 'row'; // row percentages
}
