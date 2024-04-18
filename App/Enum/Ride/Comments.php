<?php

namespace App\Enum\Ride;

enum Comments : int
	{
	use \App\Enum\Name;

	case ENABLED = 0;
	case DISABLED = 1;
	case DISABLED_AND_HIDDEN = 2;
	}

