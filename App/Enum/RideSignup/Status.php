<?php

namespace App\Enum\RideSignup;

enum Status : int
	{
	use \App\Enum\Name;

	case REMOVE = 0;
	case DEFINITELY_RIDING = 1;
	case PROBABLY_RIDING = 2;
	case WAIT_LIST = 3;
	case DEFINITELY_NOT_RIDING = 4;
	case CANCELLED = 5;
	}

