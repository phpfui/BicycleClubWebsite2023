<?php

namespace App\Enum\Store;

enum Type : int
	{
	use \App\Enum\Name;

	case DISCOUNT_CODE = 2;
	case EVENT = 3;
	case GENERAL_ADMISSION = 1;
	case MEMBERSHIP = 4;
	case ORDER = 5;
	case STORE = 0;
	}
