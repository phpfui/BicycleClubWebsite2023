<?php

namespace App\Enum\Event;

enum MembersOnly : int
	{
	use \App\Enum\Name;

	case PUBLIC = 0;
	case MEMBERS_ONLY = 1;
	case FREE_MEMBERSHIP = 2;
	case PAID_MEMBERSHIP = 3;
	}
