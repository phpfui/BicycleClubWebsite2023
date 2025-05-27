<?php

namespace App\Enum\RideSignup;

enum Attended : int
	{
	use \App\Enum\Name;

	case SIGNED_UP = 0;
	case NO_SHOW = 1;
	case CONFIRMED = 2;
	}

