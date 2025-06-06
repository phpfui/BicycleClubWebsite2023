<?php

namespace App\Enum\Event;

enum MembersOnly : int
	{
	use \App\Enum\Name;

	case FREE_MEMBERSHIP = 2;
	case MEMBERS_ONLY = 1;
	case PAID_MEMBERSHIP = 3;
	case PUBLIC = 0;
	}
