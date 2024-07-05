<?php

namespace App\Enum;

enum FolderType : int
	{
	use \App\Enum\Name;

	case FILE = 1;
	case PHOTO = 0;
	case STORE = 2;
	case VIDEO = 3;
	}
