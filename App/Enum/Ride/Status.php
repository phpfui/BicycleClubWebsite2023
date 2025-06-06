<?php

namespace App\Enum\Ride;

enum Status : int
	{
	use \App\Enum\Name;

	case CANCELLED_FOR_WEATHER = 1;
	case COMPLETED = 5;
	case CUT_SHORT = 4;
	case LEADER_OPTED_OUT = 3;
	case NO_RIDERS_SHOWED = 2;
	case NOT_YET = 0;
	}
