<?php

namespace App\Enum\RideComment;

enum Delivery : int
	{
	use \App\Enum\Name;

	case BOTH = 3;
	case EMAIL = 1;
	case TEXT = 2;
	}
