<?php

namespace App\Enum\Admin;

enum PublicPageVisibility : int
	{
	use \App\Enum\Name;

	case MEMBER_ONLY = 2;
	case NO_OUTSIDE_LINKS = 1;
	case PUBLIC = 0;
	}
