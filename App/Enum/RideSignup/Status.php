<?php

namespace App\Enum\RideSignup;

enum Status : int
	{
	use \App\Enum\Name;

	case REMOVE = 0;
	case DEFINITELY_RIDING = 1;
	case PROBABLY_RIDING = 2;
//	case POSSIBLY_RIDING = 3;
	case WAIT_LIST = 4;
	case DEFINITELY_NOT_RIDING = 5;
	case CANCELLED = 6;
	}

