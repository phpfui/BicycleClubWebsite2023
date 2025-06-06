<?php

namespace App\Enum\RideSignup;

enum Attended : int
	{
	use \App\Enum\Name;

	case CONFIRMED = 2;
	case NO_SHOW = 1;
	case SIGNED_UP = 0;
	}
