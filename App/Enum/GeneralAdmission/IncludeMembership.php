<?php

namespace App\Enum\GeneralAdmission;

enum IncludeMembership : int
	{
	use \App\Enum\Name;

	case EXTEND_MEMBERSHIP = 2;
	case NEW_MEMBERS_ONLY = 1;
	case NO = 0;
	case RENEW_MEMBERSHIP = 3;
	}
