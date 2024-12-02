<?php

namespace App\Enum\RideComment;

enum Delivery : int
	{
	use \App\Enum\Name;

	case EMAIL = 1;
	case TEXT = 2;
	case BOTH = 3;
	}

