<?php

namespace App\Enum\GeneralAdmission;

enum EventPicker
	{
	use \App\Enum\Name;

	case LINK;
	case MULTIPLE;
	case SINGLE;
	case SINGLE_SELECT;
	case TABLE;
	}

