<?php

namespace App\Enum\Forum;

enum SubscriptionType : int
	{
	use \App\Enum\Name;

	case UNSUBSCRIBE = 0;
	case VIEW_ON_WEB = 1;
	case INDIVIDUAL_EMAILS = 2;
	case DAILY_DIGEST_EMAIL = 3;
	}

