<?php

namespace App\Enum;

enum HomeNotification : int
	{
	use \App\Enum\Name;

	case CONTENT = 1;
	case CUESHEET = 2;
	case EVENT = 3;
	case HOME_PAGE_HEADER = 9;
	case MEMBER_OF_MONTH = 4;
	case NEWSLETTER = 5;
	case POLL = 6;
	case RIDE = 7;
	case VOLUNTEER = 8;

	public function getSettingName() : string
		{
		return \str_replace(' ', '_', 'HomePage' . $this->name());
		}
	}
