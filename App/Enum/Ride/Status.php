<?php

namespace App\Enum\Ride;

enum Status : int
	{
	use \App\Enum\Name;

	case NOT_YET = 0;
	case CANCELLED_FOR_WEATHER = 1;
	case NO_RIDERS_SHOWED = 2;
	case LEADER_OPTED_OUT = 3;
	case CUT_SHORT = 4;
	case COMPLETED = 5;
	}
