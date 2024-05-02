<?php

namespace App\Enum\Admin;

enum PublicPageVisibility : int
	{
	use \App\Enum\Name;

	case PUBLIC = 0;
	case NO_OUTSIDE_LINKS = 1;
	case MEMBER_ONLY = 2;
	}

