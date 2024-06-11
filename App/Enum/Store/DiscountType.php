<?php

namespace App\Enum\Store;

enum DiscountType : int
	{
	use \App\Enum\Name;

	case DOLLAR_AMOUNT = 0;
	case PERCENT_OFF = 1;
	}
