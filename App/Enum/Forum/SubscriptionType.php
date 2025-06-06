<?php

namespace App\Enum\Forum;

enum SubscriptionType : int
	{
	use \App\Enum\Name;

	case DAILY_DIGEST_EMAIL = 3;
	case INDIVIDUAL_EMAILS = 2;
	case UNSUBSCRIBE = 0;
	case VIEW_ON_WEB = 1;
	}
